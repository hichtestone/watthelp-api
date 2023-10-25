<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Permission;
use App\Entity\User;
use App\Manager\ContractManager;
use App\Manager\InvoiceManager;
use App\Manager\PermissionManager;
use App\Manager\UserManager;

class UserService
{
    private UserManager $userManager;
    private PermissionManager $permissionManager;
    private ContractManager $contractManager;
    private InvoiceManager $invoiceManager;
    private LogService $logService;

    public function __construct(
        UserManager $userManager,
        PermissionManager $permissionManager,
        ContractManager $contractManager,
        InvoiceManager $invoiceManager,
        LogService $logService
    ) {
        $this->userManager = $userManager;
        $this->permissionManager = $permissionManager;
        $this->contractManager = $contractManager;
        $this->invoiceManager = $invoiceManager;
        $this->logService = $logService;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function shouldSendImportReminder(User $user): bool
    {
        if (!$this->permissionManager->hasPermissions($user, [Permission::IMPORT_INVOICE])) {
            return false;
        }

        $minimumInvoicePeriod = $this->contractManager->getMinimumInvoicePeriod($user->getClient());
        if (is_null($minimumInvoicePeriod)) {
            return false;
        }

        $emittedAtThreshold = (new \DateTime())->sub(new \DateInterval("P{$minimumInvoicePeriod}M"));
        if ($this->invoiceManager->hasInvoicesEmittedAfter($user->getClient(), $emittedAtThreshold)) {
            return false;
        }

        return true;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendImportReminder(User $user): void
    {
        $this->logService->sendNotification($user, null, 'Aucun import de facture n\'a été effectué récemment, merci d\'importer les nouvelles factures.');
    }
}