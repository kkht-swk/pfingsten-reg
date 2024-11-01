<?php

namespace App\Controller;

use App\Entity\PlayerInfo;
use App\Form\PlayerInfoType;
use App\Repository\PlayerInfoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class PlayerInfoController extends AbstractController
{

    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    private function sendEmail(PlayerInfo $pi) {
        $ko = $pi->getKontakt();
        $ac = $pi->getAccount();

        $vn = $pi->getVorname();
        $nn = $pi->getNachname();
        $ak = $pi->getAltersklasse();
        $na = $pi->getNahrung();
        $kovn = $ko->getVorname();
        $konn = $ko->getNachname();
        $koem = $ko->getEmail();
        $koph = $ko->getPhone();
        $ib = $ac->getIban();
        $bi = $ac->getBic();
        $bk = $ac->getBank();
        $in = $ac->getKontoinhaber();

        $text = <<< EOD
Hallo,

Danke für die Anmeldung von $vn $nn zu unserem SWK Pfingstturnier!

Hier noch einmal die Details:

Spieler:in:
    Vorname:      $vn
    Nachname:     $nn
    Altersklasse: $ak
    Ernährung:    $na

Kontaktperson:
    Vorname:      $kovn
    Nachname:     $konn
    email:        $koem
    Telefon:      $koph

Bankverbindung:
    IBAN:         $ib
    BIC:          $bi
    Bank:         $bk
    Inhaber:in:   $in

Bei Rückfragen oder Anpassungen melde Dich bitte unter <pfingsten@kkht.de>

Beste Grüße vom Orga-Team!
EOD;

        $email = (new Email())
            ->from('SWK Pfingstturnier <pfingsten@kkht.de>')
            ->to($kovn . ' ' . $konn . '<' . $koem . '>')
            // ->cc('cc@example.com')
            // ->bcc('pfingsten@kkht.de')
            // ->priority(Email::PRIORITY_HIGH)
            ->subject('SWK Pfingstturnier: Registrierung ' . $pi->getVorname() . ' ' . $pi->getNachname())
            ->text($text)
            // ->html('<p>See Twig integration for better HTML integration!</p>')
            ;

        $email->bcc('pfingsten@kkht.de');

        // if ($_ENV['APP_ENV'] === 'prod') {
        //     $email->bcc('pfingsten@kkht.de');
        // }
    
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

                $repos->save($pi, true);
                $this->sendEmail($pi);

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

    #[Route('/player/summary/{hashkey}', name: 'app_player_summary')]
    public function summary(PlayerInfo $pi): Response
    {
        if ($pi != null) {
            return $this->render('player_info/player_summary.html.twig', [
                'playerInfo' => $pi,
            ]);
        }
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
    public function csv(PlayerInfoRepository $repos): Response
    {
        // $altersklassen = [ 'wU12', 'mU12', 'wU14', 'mU14' ];
        // $tiMap = [];
        // foreach ($altersklassen as $ak) {
        //     // $piMap[$ak] = $repos->findByField('altersklasse', $ak);
        //     $tiMap[$ak] = $repos->findBy(
        //         [ 'altersklasse' => $ak ],
        //         [ 'verein' => 'ASC' ]
        //     );
        // }

        $piList = $repos->findBy([], ['altersklasse' => 'ASC', 'nachname' => 'ASC']);


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
                $b->getIban(),
                $b->getBank(),
                $b->getBic(),
                $b->getKontoinhaber()
            );
            $records[] = $p;
        }


        fputcsv($fd, $headers);
        foreach($records as $record) {
            fputcsv($fd, $record);
        }

        rewind($fd);
        $csv = stream_get_contents($fd);
        fclose($fd); // releases the memory (or tempfile)

        // https://symfony.com/doc/6.4/components/http_foundation.html#serving-files
        $response = new Response($csv);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'playerlist.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }


    #[Route('/player/delete/{hashkey}', name: 'app_player_delete')]
    public function delete(PlayerInfo $pi, 
        PlayerInfoRepository $repos,
        LoggerInterface $logger): Response
    {
        $repos->delete($pi, true);
        $logger->log('WARNING', 'Deleting user ' . $pi->getId() . ' - ' . $pi->getVorname() . ' ' . $pi->getNachname());
        return $this->redirectToRoute('app_player_list');
    }
}
