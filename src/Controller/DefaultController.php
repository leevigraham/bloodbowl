<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(DataProvider $dataProvider): Response
    {
        return $this->render('index.html.twig', [
            'data' => $dataProvider
        ]);
    }

    #[Route('/reference', name: 'reference')]
    public function reference(DataProvider $dataProvider): Response
    {
        return $this->render('reference.html.twig', [
            'data' => $dataProvider
        ]);
    }
    
    #[Route('/rule-book', name: 'rule-book')]
    public function rules(DataProvider $dataProvider): Response
    {
        return $this->render('rule-book.html.twig', [
            'data' => $dataProvider
        ]);
    }
}