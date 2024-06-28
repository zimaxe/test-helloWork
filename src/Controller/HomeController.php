<?php

declare(strict_types=1);

namespace App\Controller;

use App\Interface\JobOfferServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly JobOfferServiceInterface $jobOfferService
    ) {
    }

    /** @throws \Exception  */
    #[Route('/{page<\d+>?1}', name: 'home_page')]
    public function _invoke(#[MapQueryParameter] int $page = 1): Response
    {
        $page = filter_var($page, \FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

        try {
            $jobOffers = $this->jobOfferService->getJobOffers($page);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching job offers', ['exception' => $e]);
            throw new \Exception('Error fetching job offers. Please try again later.');
        }

        return $this->render('home.html.twig', [
            'jobs' => $jobOffers,
            'page' => $page,
        ]);
    }
}
