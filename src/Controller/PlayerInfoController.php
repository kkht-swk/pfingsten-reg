<?php

namespace App\Controller;

use App\Config;
use App\Entity\PlayerInfo;
use App\Form\PlayerInfoType;
use App\Repository\PlayerInfoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class PlayerInfoController extends AbstractController
{

    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    private function sendHtmlEmail(PlayerInfo $pi) {
        $ko = $pi->getKontakt();
        $ac = $pi->getAccount();

        $email = (new TemplatedEmail())
            ->from('SWK Pfingstturnier <pfingsten@kkht.de>')
            ->to($ko->getVorname() . ' ' . $ko->getNachname() . '<' . $ko->getEmail() . '>')
            // ->cc('cc@example.com')
            ->bcc('pfingsten@kkht.de')
            // ->priority(Email::PRIORITY_HIGH)
            ->subject('SWK Pfingstturnier: Registrierung ' . $pi->getVorname() . ' ' . $pi->getNachname())
            ->htmlTemplate('email/player_reg.html.twig')
            ->textTemplate('email/player_reg.txt.twig')
            ->locale('de')
            ->context([
                'player' => $pi,
                'contact' => $ko,
                'account' => $ac
            ])
        ;

        $this->mailer->send($email);
    }

    #[Route('/player/register', name: 'app_player_new')]
    public function new(Request $request, PlayerInfoRepository $repos): Response
    {
        $pi = new PlayerInfo();
        $form = $this->createForm(PlayerInfoType::class, $pi);
        $form->handleRequest($request);

        $hasErrors = false;
        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                // Check for duplicate submission (submitted within last 10 seconds with same name + altersklasse)
                $recentSubmission = $repos->createQueryBuilder('p')
                    ->where('p.vorname = :vorname')
                    ->andWhere('p.nachname = :nachname')
                    ->andWhere('p.altersklasse = :altersklasse')
                    ->andWhere('p.createdAt > :recentTime')
                    ->setParameter('vorname', $pi->getVorname())
                    ->setParameter('nachname', $pi->getNachname())
                    ->setParameter('altersklasse', $pi->getAltersklasse())
                    ->setParameter('recentTime', new DateTimeImmutable('-10 seconds'))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($recentSubmission) {
                    // Duplicate detected - redirect to existing submission
                    return $this->redirectToRoute('app_player_summary',
                        ['hashkey' => $recentSubmission->getHashkey()]
                    );
                }

                $repos->save($pi, true);
                $this->sendHtmlEmail($pi);

                return $this->redirectToRoute('app_player_summary',
                    ['hashkey' => $pi->getHashkey()]
                );
            }
            else {
                $hasErrors = true;
            }
        }

        return $this->render('player_info/player_form.html.twig', [
            'hasErrors' => $hasErrors,
            'playerInfo' => $pi,
            'form' => $form
        ]);
    }

    #[Route('/player/summary/{hashkey:playerInfo}', name: 'app_player_summary')]
    public function summary(PlayerInfo $playerInfo): Response
    {
        return $this->render('player_info/player_summary.html.twig', [
            'playerInfo' => $playerInfo,
        ]);
    }

    #[Route('/player/list', name: 'app_player_list')]
    public function list(PlayerInfoRepository $repos): Response
    {
        $altersklassen = [ 'wU12', 'mU12', 'wU14', 'mU14' ];
        $piMap = [];
        foreach ($altersklassen as $ak) {
            // $piMap[$ak] = $repos->findByField('altersklasse', $ak);
            $piMap[$ak] = $repos->findBy(
                [ 'altersklasse' => $ak ],
                [ 'nachname' => 'ASC' ]
            );
        }
        // $piList = $repos->findAll();

        return $this->render('player_info/player_list.html.twig', [
            'player_map' => $piMap,
        ]);

    }

    #[Route('/player/csv', name: 'app_player_csv')]
    public function csv(PlayerInfoRepository $repos, LoggerInterface $logger): Response
    {

        $piList = $repos->findBy([], ['altersklasse' => 'ASC', 'nachname' => 'ASC']);
        $logger->log('INFO', 'Playerlist has length: ' . count($piList));


        // we use a threshold of 1 MB (1024 * 1024), it's just an example
        // https://stackoverflow.com/questions/30510941/create-csv-in-memory-email-and-remove-from-memory
        $fd = fopen('php://temp/maxmemory:1048576', 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        $headers = array('Altersklasse', 'Vorname', 'Nachname', 'Food',
            'IBAN', 'Bank', 'BIC', 'Kontoinh');

        $records = array();

        foreach ($piList as $pi) {
            $k = $pi->getKontakt();
            $b = $pi->getAccount();
            $p = array(
                $pi->getAltersklasse(),
                $pi->getVorname(),
                $pi->getNachname(),
                $pi->getNahrung(),
                Config::formatIban($b->getIban()),
                $b->getBank(),
                $b->getBic(),
                $b->getKontoinhaber()
            );
            $records[] = $p;
        }


        fputcsv($fd, $headers, separator: ";", escape: "\\");
        foreach($records as $record) {
            fputcsv($fd, $record, separator: ";", escape: "\\");
        }

        rewind($fd);
        $csv = stream_get_contents($fd);
        $csv = mb_convert_encoding($csv, 'ISO-8859-15', 'UTF-8');
        fclose($fd); // releases the memory (or tempfile)

        // https://symfony.com/doc/6.4/components/http_foundation.html#serving-files
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=ISO-8859-15');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'playerlist.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    #[Route('/player/xls', name: 'app_player_xls')]
    public function xls(PlayerInfoRepository $repos,
        LoggerInterface $logger): Response
    {
        $piList = $repos->findBy([], ['altersklasse' => 'ASC', 'nachname' => 'ASC']);
        $logger->log('INFO', 'Playerlist has length: ' . count($piList));

        $writer = new Writer();
        $writer->openToBrowser('playerlist.xlsx');

        $writer->addRow(Row::fromValues([
            'Altersklasse', 'Vorname', 'Nachname', 'Food',
            'Kontaktperson', 'Email', 'Telefon',
            'IBAN', 'Bank', 'BIC', 'Kontoinh']));

        foreach ($piList as $pi) {
            $k = $pi->getKontakt();
            $b = $pi->getAccount();

            $writer->addRow(Row::fromValues([
                $pi->getAltersklasse(),
                $pi->getVorname(),
                $pi->getNachname(),
                $pi->getNahrung(),
                $k->getVorname() . ' ' . $k->getNachname(),
                $k->getEmail(),
                $k->getPhone(),
                Config::formatIban($b->getIban()),
                $b->getBank(),
                $b->getBic(),
                $b->getKontoinhaber(),
            ]));
        }
        $writer->close();
        exit;
    }

    #[Route('/player/delete/{id}', name: 'app_player_delete')]
    public function delete(
        PlayerInfo $pi,
        PlayerInfoRepository $repos,
        LoggerInterface $logger): Response
    {
        $repos->delete($pi, true);
        $logger->log('WARNING', 'Deleting user ' . $pi->getId() . ' - ' . $pi->getVorname() . ' ' . $pi->getNachname());
        return $this->redirectToRoute('app_player_list');
    }
}
