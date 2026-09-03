<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Ubermuda\AdminBundle\Listing\ListPageRequest;
use Ubermuda\AuditBundle\Command\Admin\ListAuditLogCommand;
use Ubermuda\AuditBundle\Command\Admin\ListAuditLogHandler;
use Ubermuda\AuditBundle\Security\AuditLogVoter;

#[IsGranted(AuditLogVoter::ADMIN)]
final class ListAuditLogController extends AbstractController
{
    public function __construct(
        private readonly ListAuditLogHandler $listAuditLog,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $listRequest = ListPageRequest::fromRequest(
            $request,
            ListAuditLogHandler::ALLOWED_SORTS,
            'occurredAt',
        );

        $view = ($this->listAuditLog)(new ListAuditLogCommand(
            page: $listRequest->page,
            dir: $listRequest->dir,
            actor: $request->query->getString('q') ?: null,
            operation: $request->query->getString('operation') ?: null,
            channel: $request->query->getString('channel') ?: null,
            from: $request->query->getString('from') ?: null,
            to: $request->query->getString('to') ?: null,
        ));

        if (null !== $view->clampedPage) {
            return $this->redirectToRoute(
                'ubermuda_audit_log_list',
                [...$request->query->all(), 'page' => $view->clampedPage],
            );
        }

        return $this->render('@UbermudaAudit/admin/list.html.twig', [
            'rows' => $view->rows,
            'channels' => $view->channels,
            'total' => $view->total,
            'page' => $listRequest->page,
            'totalPages' => $view->totalPages,
            'pageList' => $view->pageList,
            'sort' => $listRequest->sort,
            'dir' => $listRequest->dir,
            'filters' => $view->filters,
        ]);
    }
}
