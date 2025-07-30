<?php

namespace App\Controller;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PropertyAgentController extends AbstractController
{

    public function __construct(
        #[Autowire(service: 'ai.agent.kraken')]
        private readonly AgentInterface $agent,
    )
    {
    }

    #[Route('/property/agent', name: 'app_property_agent')]
    public function index(): JsonResponse
    {

        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
                Please answer all user questions using the kraken tool for property analysis.
                PROMPT
            ),
            Message::ofUser('what is the closest schools to this property id eyJ1ZHBybiI6ICIxOTg0ODc1NCJ9?')
        );

        try {
            $result = $this->agent->call($messages);
        }catch (\Throwable $e) {
            dd($e);
        }


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PropertyAgentController.php',
            'result' => $result->getContent() ?? ''
        ]);
    }
}
