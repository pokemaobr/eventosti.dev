<?php

namespace App\Controller;

use App\Entity\Call4papers;
use App\Repository\Call4papersRepository;
use App\Service\ThreadsService;
use App\Service\TwitterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Eventos;
use App\Repository\EventosRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\EmailService;
use App\Service\TelegramService;
use Symfony\Component\HttpFoundation\RequestStack;

class IndexController extends AbstractController
{

    public function __construct(public RequestStack $requestStack, public EntityManagerInterface $doctrine)
    {
        $this->session = $this->requestStack->getSession();
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $hoje = new \DateTime();

        $eventos = $this->doctrine
            ->getRepository(Eventos::class)
            ->pegarEventosFuturosHabilitados($hoje);

        $call4papers = $this->doctrine
            ->getRepository(Call4papers::class)
            ->pegarCall4PapersAbertosHabilitados($hoje);

        return $this->render('index.html.twig', ['eventos' => $eventos, 'call4papers' => $call4papers, 'tipo' => [0 => 'Online', 1 => 'Presencial', 2 => 'Híbrido']]);

    }

    #[Route('/cadastrar', name: 'cadastrar')]
    public function cadastrar(): Response
    {
        return $this->render('cadastrar.html.twig');
    }

    #[Route('/cadastro', name: 'cadastro', methods: ['POST'])]
    public function cadastro(Request $request, MailerInterface $mailer): Response
    {

        $entityManager = $this->doctrine;

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
            if (!empty($request->request->get('ingresso'))) {
                $evento->setIngresso($request->request->get('ingresso'));
            }
            if (!empty($request->request->get('pago'))) {
                $evento->setPago(1);
            }
            if (!empty($request->request->get('gratuito'))) {
                $evento->setGratuito(1);
            }
            $evento->setHabilitado(0);

            $entityManager->persist($evento);
            $entityManager->flush();

            //$email = new EmailService($_ENV['USUARIO_EMAIL'], $_ENV['ENDERECO_EMAIL']);
            //$email->avisarCadastro($request->request->get('nome'), $mailer);

            return $this->redirectToRoute('cadastrar', ['status' => 1]);

        } catch (Exception) {
            return $this->redirectToRoute('cadastrar', ['status' => 2]);
        }


        return $this->render('cadastrar.html.twig', ['status' => 2]);
    }

    #[Route('/cadastrar-call4papers', name: 'cadastrar-call4papers')]
    public function cadastrarCall4papers(): Response
    {
        return $this->render('call4papers.html.twig');
    }

    #[Route('/cadastro-call4papers', name: 'cadastro-call4papers', methods: ['POST'])]
    public function cadastroCall4Papers(Request $request, MailerInterface $mailer): Response
    {

        $entityManager = $this->doctrine;

        try {
            $evento = new Call4papers();
            $evento->setNome($request->request->get('nome'));
            $evento->setTipo($request->request->get('tipo'));
            $evento->setLocal($request->request->get('local'));
            $evento->setImagem($request->request->get('imagem'));
            $evento->setDescricao($request->request->get('descricao'));
            $evento->setDataInicio(new \DateTime($request->request->get('dataInicio')));
            $evento->setDataFim(new \DateTime($request->request->get('dataFim')));
            $evento->setDataEncerramento(new \DateTime($request->request->get('dataEncerramento')));
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
            if (!empty($request->request->get('evento'))) {
                $evento->setEvento($request->request->get('evento'));
            }
            $evento->setHabilitado(0);

            $entityManager->persist($evento);
            $entityManager->flush();

            $email = new EmailService($_ENV['USUARIO_EMAIL'], $_ENV['ENDERECO_EMAIL']);
            $email->avisarCadastroCall4Papers($request->request->get('nome'), $mailer);

            return $this->redirectToRoute('cadastrar-call4papers', ['status' => 1]);

        } catch (Exception) {
            return $this->redirectToRoute('cadastrar-call4papers', ['status' => 2]);
        }


        return $this->render('call4papers.html.twig', ['status' => 2]);
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

            $eventos = $this->doctrine
                ->getRepository(Eventos::class)
                ->pegarEventosFuturos($hoje);

            $call4papers = $this->doctrine
                ->getRepository(Call4papers::class)
                ->pegarCall4PapersAbertos($hoje);

           return $this->render('habilitar.html.twig', ['eventos' => $eventos,'call4papers' => $call4papers]);

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

        $entityManager = $this->doctrine;

        try {
            $evento->setHabilitado(1);
            $entityManager->persist($evento);
            $entityManager->flush();

            //$threads = new ThreadsService();
            //$threads->enviaMensagemCadastroEvento($evento);

            $telegram = new TelegramService();
            $telegram->enviaMensagemCadastroEvento($_ENV['CHAT_ID'], $evento);

            $twitter = new TwitterService();
            $twitter->enviaMensagemCadastroEvento($evento);

            return new JsonResponse(
                ['data' => 1]
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['data' => 2,'error' => $e->getMessage()]
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

        $entityManager = $this->doctrine;

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

    #[Route('/habilitar-call4papers/{id}', name: 'habilitar-call4papers', methods: ['GET'])]
    public function habilitarCall4Papers(Call4papers $evento, Request $request): Response
    {
        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

        try {
            $evento->setHabilitado(1);
            $entityManager->persist($evento);
            $entityManager->flush();

            $telegram = new TelegramService();
            $telegram->enviaMensagemCadastroCall4Papers($_ENV['CHAT_ID'], $evento);

            //$twitter = new TwitterService();
            //$twitter->enviaMensagemCadastroEvento($evento);

            return new JsonResponse(
                ['data' => 1]
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['data' => 2,'error' => $e->getMessage()]
            );
        }

        return new JsonResponse(
            ['data' => 2]
        );
    }

    #[Route('/desabilitar-call4papers/{id}', name: 'desabilitar-call4papers', methods: ['GET'])]
    public function desabilitarCall4Papers(Call4papers $evento, Request $request): Response
    {

        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

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

    #[Route('/transformar-em-evento-gratuito/{id}', name: 'transformar-em-evento-gratuito', methods: ['GET'])]
    public function transformarEmEventoGratuito(Eventos $evento, Request $request): Response
    {
        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

        try {
            $evento->setGratuito(1);
            $entityManager->persist($evento);
            $entityManager->flush();

            return new JsonResponse(
                ['data' => 1]
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['data' => 2,'error' => $e->getMessage()]
            );
        }

        return new JsonResponse(
            ['data' => 2]
        );
    }

    #[
        Route('/transformar-em-evento-nao-gratuito/{id}', name: 'transformar-em-evento-nao-gratuito', methods: ['GET'])]
    public function transformarEmEventoNaoGratuito(Eventos $evento, Request $request): Response
    {

        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

        try {
            $evento->setGratuito(0);
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

    #[Route('/transformar-em-evento-pago/{id}', name: 'transformar-em-evento-pago', methods: ['GET'])]
    public function transformarEmEventoPago(Eventos $evento, Request $request): Response
    {
        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

        try {
            $evento->setPago(1);
            $entityManager->persist($evento);
            $entityManager->flush();

            return new JsonResponse(
                ['data' => 1]
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['data' => 2,'error' => $e->getMessage()]
            );
        }

        return new JsonResponse(
            ['data' => 2]
        );
    }

    #[
        Route('/transformar-em-evento-nao-pago/{id}', name: 'transformar-em-evento-nao-pago', methods: ['GET'])]
    public function transformarEmEventoNaoPago(Eventos $evento, Request $request): Response
    {

        $token = $this->session->get('chave');

        if (!$this->isCsrfTokenValid('habilitar', $token)) {
            return new JsonResponse(
                ['data' => 2]
            );
        }

        $entityManager = $this->doctrine;

        try {
            $evento->setPago(0);
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

