<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Eventos;
use App\Repository\EventosRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\EmailService;
use App\Service\TelegramService;

class IndexController extends AbstractController
{

    public function __construct(public SessionInterface $session)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $hoje = new \DateTime();

        $eventos = $this->getDoctrine()
            ->getRepository(Eventos::class)
            ->pegarEventosFuturosHabilitados($hoje);

        return $this->render('index.html.twig', ['eventos' => $eventos, 'tipo' => [0 => 'Online', 1 => 'Presencial', 2 => 'Híbrido']]);

    }

    #[Route('/cadastrar', name: 'cadastrar')]
    public function cadastrar(): Response
    {
        return $this->render('cadastrar.html.twig');
    }

    #[Route('/cadastro', name: 'cadastro', methods: ['POST'])]
    public function cadastro(Request $request, MailerInterface $mailer): Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        try {
            $evento = new Eventos;
            $evento->setNome($request->request->get('nome'));
            $evento->setTipo($request->request->get('tipo'));
            $evento->setLocal($request->request->get('local'));
            $evento->setImagem($request->request->get('imagem'));
            $evento->setDescricao($request->request->get('descricao'));
            $evento->setDataInicio(new \DateTime($request->request->get('dataInicio')));
            $evento->setDataFim(new \DateTime($request->request->get('dataFim')));
            $evento->setLink($request->request->get('link'));
            if (!empty($request->request->get('twitter'))) {
                $evento->setTwitter($request->request->get('twitter'));
            }
            if (!empty($request->request->get('instagram'))) {
                $evento->setInstagram($request->request->get('instagram'));
            }
            if (!empty($request->request->get('outro'))) {
                $evento->setOutro($request->request->get('outro'));
            }
            $evento->setHabilitado(0);

            $entityManager->persist($evento);
            $entityManager->flush();

            $email = new EmailService('pokemaobr', 'contato@pokemaobr.dev');
            $email->avisarCadastro($request->request->get('nome'), $mailer);

            $telegram = new TelegramService();
            $telegram->enviaMensagemCadastroEvento($_ENV['CHAT_ID'], $evento);

            return $this->redirectToRoute('cadastrar', ['status' => 1]);

        } catch (Exception) {
            return $this->redirectToRoute('cadastrar', ['status' => 2]);
        }


        return $this->render('cadastrar.html.twig', ['status' => 2]);
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {

        $image = $request->request->get('image');
        if (isset($image)) {
            $data = $image;

            $image_array_1 = explode(";", $data);

            $image_array_2 = explode(",", $image_array_1[1]);

            $data = base64_decode($image_array_2[1]);

            $imageName = time() . '.png';

            $root = $this->getParameter('kernel.project_dir');

            file_put_contents($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR . $imageName, $data);

        }

        return $this->render('upload.html.twig', ['imageName' => $imageName]);
    }

    #[Route('/logar', name: 'logar')]
    public function logar(): Response
    {
        return $this->render('logar.html.twig');

    }

    #[Route('/habilitar', name: 'habilitar', methods: ['POST'])]
    public function habilitar(Request $request): Response
    {

        $token = $request->request->get('token');

        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('habilitar', $token)) {

            $this->session->set('chave', $token);
            $chave = $request->request->get('chave');

            if ($chave != $_ENV['CHAVE_MESTRA']) {
                return $this->render('logar.html.twig', ['validate' => false]);
            }

            $hoje = new \DateTime();

            $eventos = $this->getDoctrine()
                ->getRepository(Eventos::class)
                ->pegarEventosFuturos($hoje);

            return $this->render('habilitar.html.twig', ['eventos' => $eventos]);

        }

        return $this->render('logar.html.twig', ['validate' => false]);


    }

    #[Route('/habilitar-evento/{id}', name: 'habilitar-evento', methods: ['GET'])]
    public function habilitarEvento(Eventos $evento, Request $request): Response
    {
        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->getDoctrine()->getManager();

        try {
            $evento->setHabilitado(1);
            $entityManager->persist($evento);
            $entityManager->flush();
            return new JsonResponse(
                ['data' => 1]
            );

        } catch (\Exception) {
            return new JsonResponse(
                ['data' => 2]
            );
        }


        return new JsonResponse(
            ['data' => 2]
        );
    }

    #[
        Route('/desabilitar-evento/{id}', name: 'desabilitar-evento', methods: ['GET'])]
    public function desabilitarEvento(Eventos $evento, Request $request): Response
    {

        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->getDoctrine()->getManager();

        try {
            $evento->setHabilitado(0);
            $entityManager->persist($evento);
            $entityManager->flush();
            return new JsonResponse(
                ['data' => 1]
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        return new JsonResponse(
            ['data' => 2]
        );
    }
}

