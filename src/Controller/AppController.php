<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\Finder\CommandFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Webmozart\Assert\Assert;

class AppController
{
    public function __construct(
        private readonly CommandFinder $finder,
        private readonly Environment $template,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $namespace = $request->attributes->get('namespace');
        Assert::nullOrString($namespace);

        $commands = $this->finder->findByNamespace($namespace);

        if ([] === $commands) {
            throw new NotFoundHttpException('Given Console namespace does not exist.');
        }

        return new Response($this->template->render('@ConsoleUi/console-ui.html.twig', [
            'commands' => $commands,
            'menu_items' => $this->finder->findAllNamespaces(),
        ]));
    }
}
