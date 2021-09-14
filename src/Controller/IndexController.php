<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Eventos;

class IndexController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $hoje = new \DateTime();

       // $eventos = $entityManager->getRepository('App\\Entity\\Eventos')->findBy(['habilitado' => 1, 'dataFim'  $hoje], ['dataInicio' => 'asc', 'dataFim' => 'asc']);

        $query = $entityManager->createQuery('
        Select e
        FROM App\Entity\Eventos e
        WHERE e.habilitado = 1 AND e.dataFim >= :hoje
        ORDER BY e.dataFim ASC')
            ->setParameter('hoje', $hoje);

        $eventos = $query->getResult();

        return $this->render('index.html.twig', ['eventos' => $eventos, 'tipo' => [0 => 'Online',1 => 'Presencial', 2 => 'Híbrido']]);

    }

    #[Route('/cadastrar', name: 'cadastrar')]
    public function cadastrar(): Response
    {
        return $this->render('cadastrar.html.twig');
    }

    #[Route('/cadastro', name: 'cadastro', methods: ['POST'])]
    public function cadastro(Request $request): Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        try {
            $eventos = new Eventos;
            $eventos->setNome($request->request->get('nome'));
            $eventos->setTipo($request->request->get('tipo'));
            $eventos->setLocal($request->request->get('local'));
            $eventos->setImagem($request->request->get('imagem'));
            $eventos->setDescricao($request->request->get('descricao'));
            $eventos->setDataInicio(new \DateTime($request->request->get('dataInicio')));
            $eventos->setDataFim(new \DateTime($request->request->get('dataFim')));
            $eventos->setLink($request->request->get('link'));
            if (!empty($request->request->get('twitter'))) {
                $eventos->setTwitter($request->request->get('twitter'));
            }
            if (!empty($request->request->get('instagram'))) {
                $eventos->setInstagram($request->request->get('instagram'));
            }
            if (!empty($request->request->get('outro'))) {
                $eventos->setOutro($request->request->get('outro'));
            }
            $eventos->setHabilitado(0);


            $entityManager->persist($eventos);
            $entityManager->flush();

            return $this->redirectToRoute('cadastrar',['status' => 1]);

        } catch (Exception) {
            return $this->redirectToRoute('cadastrar',['status' => 2]);
        }


        return $this->render('cadastrar.html.twig',['status' => 2]);
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
}