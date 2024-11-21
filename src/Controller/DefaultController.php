<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(DataProvider $dataProvider): Response
    {
        return $this->render('index.html.twig', [
            'data' => $dataProvider
        ]);
    }
}