<?php

namespace App\Controller;

use App\Entity\TeamInfo;
use App\Form\TeamInfoType;
use App\Repository\TeamInfoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class TeamInfoController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private SluggerInterface $slugger
    ) {
    }

    private function sendHtmlEmail(TeamInfo $ti, string $locale) {

        $myurl = $this->generateUrl('app_team_edit', [
            'hashkey' => $ti->getHashkey()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $ko = $ti->getKontakt();
        $ac = $ti->getAccount();

        $email = (new TemplatedEmail())
            ->from('SWK Pfingstturnier <pfingsten@kkht.de>')
            ->to($ko->getVorname() . ' ' . $ko->getNachname() . '<' . $ko->getEmail() . '>')
            // ->cc('cc@example.com')
            ->bcc('pfingsten@kkht.de')
            // ->priority(Email::PRIORITY_HIGH)
            ->subject('SWK Pfingstturnier: Registrierung ' .  $ti->getVerein() . ' (' . $ti->getAltersklasse() . ')')
            ->htmlTemplate('email/team_reg.html.twig')
            ->textTemplate('email/team_reg.txt.twig')
            ->locale('de')
            ->context([
                'team' => $ti,
                'contact' => $ko,
                'account' => $ac,
                'editLink' => $myurl
            ])
        ;
    
        $this->mailer->send($email);
    }

    private function storePic(?UploadedFile $file, string $fname, string $prefix): ?string 
    {
        // $prefix is "logo" or "team"
        if ($file) {
            $newFilename = $prefix . '_' . $fname . '_' . uniqid() . '.' .$file->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $file->move($this->getParameter('app.upload_dir'),$newFilename);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return null;
            }
            return $newFilename;
        }
        return null;
    }

    private function saveTeamInfo(
        Form $form,
        TeamInfo $ti,
        TeamInfoRepository $repos) 
    {
            $fname = $this->slugger->slug($ti->getVerein() . "_" . $ti->getAltersklasse());
            $logoPath = $this->storePic($form->get('logo')->getData(), $fname, 'logo');
            if ($logoPath) {
                $ti->setLogoPath($logoPath);
            }
            $picturePath = $this->storePic($form->get('picture')->getData(), $fname, 'team');
            if ($picturePath) {
                $ti->setPicturePath($picturePath);
            }

            $repos->save($ti, true);
    }

    #[Route('/team/register', name: 'app_team_new_default')]
    #[Route('/team/register/{hashkey}', name: 'app_team_edit_default')]
    #[Route('/team/list', name: 'app_team_list_default')]
    #[Route('/team/master/{hashkey}', name: 'app_team_master_default')]
    #[Route('/team/delete/{hashkey}', name: 'app_team_delete_default')]
    #[Route('/team/csv', name: 'app_team_csv_default')]
    public function index(
        ?string $hashkey,
        Request $request) : Response
    {
        $locale = $request->getLocale() ?: 'de';
        $route = $request->attributes->get('_route');
        $newroute = str_replace('_default', '', $route);

        return $this->redirectToRoute($newroute, [ 
            'hashkey' => $hashkey,
            '_locale' => $locale
        ]);
    }
        


    #[Route('/{_locale}/team/register', name: 'app_team_new',
        requirements: [ '_locale' => 'en|de|nl' ])]
    #[Route('/{_locale}/team/register/{hashkey}', name: 'app_team_edit',
        requirements: [ '_locale' => 'en|de|nl' ])]
    #[Route('/{_locale}/team/master/{hashkey}', name: 'app_team_master',
        requirements: [ '_locale' => 'en|de|nl' ])]
    public function register(
        ?string $hashkey,
        Request $request, 
        TeamInfoRepository $repos): Response
    {
        $logoPath = 'pics/LogoPlaceholder.png';
        $picturePath = 'pics/TeamPlaceholder.jpeg';
        $currcost = 0;

        $ti = new TeamInfo();
        if ($hashkey) {
            $ti = $repos->findOneBy([ 'hashkey' => $hashkey ]);
        }

        if ($ti) {
            if ($ti->getLogoPath() != null) {
                $logoPath = 'uploads/' . $ti->getLogoPath();
            }
            if ($ti->getPicturePath() != null) {
                $picturePath = 'uploads/' . $ti->getPicturePath();
            }
        }

        $form = $this->createForm(TeamInfoType::class, $ti);
        $form->handleRequest($request);

        $x1 = intval($form->get('spielervegan')->getData());
        $x2 = intval($form->get('spielerfleisch')->getData());
        $x3 = intval($form->get('betreuervegan')->getData());
        $x4 = intval($form->get('betreuerfleisch')->getData());
        
        $cntSpieler = $x1 + $x2;
        $cntBetreuer = $x3 + $x4;
        $currcost = 90 * ($x1 + $x2 + $x3 + $x4);

        $hasErrors = false;

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $this->saveTeamInfo($form, $ti, $repos);

                // if I am mastering, just return to list
                if ($request->attributes->get('_route') === 'app_team_master') {
                    return $this->redirectToRoute('app_team_list');
                }

                // else: send email and show summary
                $this->sendHtmlEmail($ti, $request->getLocale());
                return $this->redirectToRoute('app_team_summary', [
                    'hashkey' => $ti->getHashkey()] );
            }
            else {
                $hasErrors = true;
            }
        } 

        return $this->render('team_info/team_form.html.twig', [
            'hasErrors' => $hasErrors,
            'logoPath' => $logoPath,
            'picturePath' => $picturePath,
            'cnt_spieler' => $cntSpieler,
            'cnt_betreuer' => $cntBetreuer,
            'curr_cost' => $currcost,
            'form' => $form,
        ]);
    }


    #[Route('/{_locale}/team/summary/{hashkey}', name: 'app_team_summary',
        requirements: [ '_locale' => 'en|de|nl' ])]
    public function summary(
        #[MapEntity(mapping: ['hashkey' => 'hashkey'])]
        TeamInfo $ti): Response
    {
        return $this->render('team_info/team_summary.html.twig', [
            'controller_name' => 'TeamInfoController',
            'teamInfo' => $ti,
        ]);
        // if ($ti != null) {
        //     return $this->render('team_info/team_summary.html.twig', [
        //         'controller_name' => 'TeamInfoController',
        //         'teamInfo' => $ti,
        //     ]);
        // }
    }

    #[Route('/{_locale}/team/list', name: 'app_team_list',
        requirements: [ '_locale' => 'de' ],
    )]
    public function list(TeamInfoRepository $repos): Response
    {
        $altersklassen = [ 'wU12', 'mU12', 'wU14', 'mU14' ];
        $tiMap = [];
        foreach ($altersklassen as $ak) {
            // $piMap[$ak] = $repos->findByField('altersklasse', $ak);
            $tiMap[$ak] = $repos->findBy(
                [ 'altersklasse' => $ak ],
                [ 'verein' => 'ASC' ]
            );
        }

        return $this->render('team_info/team_list.html.twig', [
            'team_map' => $tiMap,
        ]);

    }

    #[Route('/{_locale}/team/delete/{id}', name: 'app_team_delete')]
    public function delete(TeamInfo $ti, 
        TeamInfoRepository $repos,
        LoggerInterface $logger): Response
    {
        $repos->delete($ti, true);
        $logger->log('WARNING', 'Deleting team ' . $ti->getId() . ' - ' . 
            $ti->getVerein() . ' (' . $ti->getAltersklasse() . ')');
        return $this->redirectToRoute('app_team_list');
    }

    #[Route('/{_locale}/team/csv', name: 'app_team_csv')]
    public function csv(TeamInfoRepository $repos,
        LoggerInterface $logger): Response
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

        $tiList = $repos->findBy([], ['altersklasse' => 'ASC', 'verein' => 'ASC']);
        $logger->log('INFO', 'Teamlist has length: ' . count($tiList));


        // we use a threshold of 1 MB (1024 * 1024), it's just an example
        // https://stackoverflow.com/questions/30510941/create-csv-in-memory-email-and-remove-from-memory
        $fd = fopen('php://temp/maxmemory:1048576', 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        $headers = array('Altersklasse', 'Verein', 'Ankunft', 'Gäste',
            'Spieler vegan', 'Spieler Fleisch', 'Betreuer vegan', 'Betreuer Fleisch', 
            'Kontakt', 'email', 'tel',
            'IBAN', 'BIC', 'Bank', 'Inhaber'
        );

        $records = array();

        foreach ($tiList as $ti) {
            $k = $ti->getKontakt();
            $b = $ti->getAccount();
            $t = array(
                $ti->getAltersklasse(),
                $ti->getVerein(),
                $ti->getAnkunftszeit(),
                $ti->getGaeste(),
                $ti->getSpielerVegan(),
                $ti->getSpielerFleisch(),
                $ti->getBetreuerVegan(),
                $ti->getBetreuerFleisch(),
                $k->getVorname() . ' ' . $k->getNachname(),
                $k->getEmail(),
                $k->getPhone(),
                $b->getIban(),
                $b->getBic(),
                $b->getBank(),
                $b->getKontoinhaber(),
            );
            $records[] = $t;
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
            'teamlist.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
