<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PermissionRepository::class)
 */
class Permission
{
    public const USER_VIEW = 'system.user.view';
    public const USER_EDIT = 'system.user.edit';
    public const USER_EDIT_PASSWORD = 'system.user.edit_password';
    public const USER_DELETE = 'system.user.delete';

    public const TAX_VIEW = 'system.tax.view';
    public const TAX_EDIT = 'system.tax.edit';
    public const TAX_DELETE = 'system.tax.delete';

    public const PRICING_VIEW = 'system.pricing.view';
    public const PRICING_EDIT = 'system.pricing.edit';
    public const PRICING_DELETE = 'system.pricing.delete';

    public const ROLE_VIEW = 'system.role.view';
    public const ROLE_EDIT = 'system.role.edit';
    public const ROLE_DELETE = 'system.role.delete';

    public const PERMISSION_VIEW = 'system.permission.view';

    public const CONTRACT_VIEW = 'management.contract.view';
    public const CONTRACT_EDIT = 'management.contract.edit';
    public const CONTRACT_DELETE = 'management.contract.delete';

    public const DELIVERY_POINT_VIEW = 'management.delivery_point.view';
    public const DELIVERY_POINT_EDIT = 'management.delivery_point.edit';
    public const DELIVERY_POINT_DELETE = 'management.delivery_point.delete';
    public const DELIVERY_POINT_MAP = 'management.delivery_point.map';

    public const INVOICE_VIEW = 'management.invoice.view';
    public const INVOICE_EDIT = 'management.invoice.edit';
    public const INVOICE_DELETE = 'management.invoice.delete';
    public const INVOICE_ANALYZE = 'management.invoice.analyze';

    public const ANALYSIS_VIEW = 'management.analysis.view';
    public const ANALYSIS_DELETE = 'management.analysis.delete';

    public const BUDGET_VIEW = 'management.budget.view';
    public const BUDGET_EDIT = 'management.budget.edit';
    public const BUDGET_DELETE = 'management.budget.delete';

    public const ANOMALY_VIEW = 'management.anomaly.view';
    public const ANOMALY_EDIT = 'management.anomaly.edit';
    public const ANOMALY_DELETE = 'management.anomaly.delete';

    public const ANOMALY_NOTE_EDIT = 'management.anomaly_note.edit';

    public const EXPORT_BUDGET = 'management.export.budget';
    public const EXPORT_ANOMALY = 'management.export.anomaly';
    public const EXPORT_DELIVERY_POINT = 'management.export.delivery_point';

    public const IMPORT_BUDGET = 'management.import.budget';
    public const IMPORT_INVOICE = 'management.import.invoice';
    public const IMPORT_SCOPE = 'management.import.scope';
    public const IMPORT_FILE = 'management.import.file';
    public const IMPORT_REPORT_VIEW = 'management.import_report.view';
    public const PRICING_IMPORT = 'system.pricing.import';

    public const PRICING_EXPORT = 'system.pricing.export';

    public const DASHBOARD_VIEW = 'other.dashboard.view';

    public const AVAILABLE_PERMISSION_CODES = [
        self::USER_VIEW,
        self::USER_EDIT,
        self::USER_EDIT_PASSWORD,
        self::USER_DELETE,
        self::TAX_VIEW,
        self::TAX_EDIT,
        self::TAX_DELETE,
        self::PRICING_VIEW,
        self::PRICING_EDIT,
        self::PRICING_DELETE,
        self::ROLE_VIEW,
        self::ROLE_EDIT,
        self::ROLE_DELETE,
        self::PERMISSION_VIEW,
        self::CONTRACT_VIEW,
        self::CONTRACT_EDIT,
        self::CONTRACT_DELETE,
        self::DELIVERY_POINT_VIEW,
        self::DELIVERY_POINT_EDIT,
        self::DELIVERY_POINT_DELETE,
        self::DELIVERY_POINT_MAP,
        self::INVOICE_VIEW,
        self::INVOICE_EDIT,
        self::INVOICE_DELETE,
        self::INVOICE_ANALYZE,
        self::ANALYSIS_VIEW,
        self::ANALYSIS_DELETE,
        self::BUDGET_VIEW,
        self::BUDGET_EDIT,
        self::BUDGET_DELETE,
        self::ANOMALY_VIEW,
        self::ANOMALY_EDIT,
        self::ANOMALY_DELETE,
        self::ANOMALY_NOTE_EDIT,
        self::EXPORT_BUDGET,
        self::EXPORT_ANOMALY,
        self::EXPORT_DELIVERY_POINT,
        self::PRICING_EXPORT,
        self::IMPORT_BUDGET,
        self::IMPORT_INVOICE,
        self::IMPORT_SCOPE,
        self::IMPORT_FILE,
        self::IMPORT_REPORT_VIEW,
        self::DASHBOARD_VIEW,
        self::PRICING_IMPORT
    ];

    public const AVAILABLE_PERMISSIONS = [
        self::USER_VIEW => ['description' => 'Voir les utilisateurs'],
        self::USER_EDIT => ['description' => 'Modifier un utilisateur'],
        self::USER_EDIT_PASSWORD => ['description' => 'Modifier le mot de passe d\'un utilisateur'],
        self::USER_DELETE => ['description' => 'Supprimer un utilisateur'],
        self::TAX_VIEW => ['description' => 'Voir les taxes'],
        self::TAX_EDIT => ['description' => 'Modifier une taxe'],
        self::TAX_DELETE => ['description' => 'Supprimer une taxe'],
        self::PRICING_VIEW => ['description' => 'Voir les tarifs'],
        self::PRICING_EDIT => ['description' => 'Modifier un tarif'],
        self::PRICING_DELETE => ['description' => 'Supprimer un tarif'],
        self::ROLE_VIEW => ['description' => 'Voir les rôles'],
        self::ROLE_EDIT => ['description' => 'Modifier un rôle'],
        self::ROLE_DELETE => ['description' => 'Supprimer un rôle'],
        self::PERMISSION_VIEW => ['description' => 'Voir les permissions'],
        self::CONTRACT_VIEW => ['description' => 'Voir les contrats'],
        self::CONTRACT_EDIT => ['description' => 'Modifier un contrat'],
        self::CONTRACT_DELETE => ['description' => 'Supprimer un contrat'],
        self::DELIVERY_POINT_VIEW => ['description' => 'Voir les points de livraison'],
        self::DELIVERY_POINT_EDIT => ['description' => 'Modifier un point de livraison'],
        self::DELIVERY_POINT_DELETE => ['description' => 'Supprimer un point de livraison'],
        self::DELIVERY_POINT_MAP => ['description' => 'Voir la cartographie'],
        self::INVOICE_VIEW => ['description' => 'Voir les factures'],
        self::INVOICE_EDIT => ['description' => 'Modifier une facture'],
        self::INVOICE_DELETE => ['description' => 'Supprimer une facture'],
        self::INVOICE_ANALYZE => ['description' => 'Analyser une facture'],
        self::ANALYSIS_VIEW => ['description' => 'Voir les analyses'],
        self::ANALYSIS_DELETE => ['description' => 'Supprimer les analyses'],
        self::BUDGET_VIEW => ['description' => 'Voir les budgets'],
        self::BUDGET_EDIT => ['description' => 'Modifier un budget'],
        self::BUDGET_DELETE => ['description' => 'Supprimer un budget'],
        self::ANOMALY_VIEW => ['description' => 'Voir les alertes'],
        self::ANOMALY_EDIT => ['description' => 'Modifier une alerte'],
        self::ANOMALY_DELETE => ['description' => 'Supprimer une alerte'],
        self::ANOMALY_NOTE_EDIT => ['description' => 'Modifier un commentaire'],
        self::EXPORT_BUDGET => ['description' => 'Exporter des budgets'],
        self::EXPORT_ANOMALY => ['description' => 'Exporter des alertes'],
        self::EXPORT_DELIVERY_POINT => ['description' => 'Exporter des points de livraison'],
        self::PRICING_EXPORT => ['description' => 'Exporter les tarifs'],
        self::IMPORT_BUDGET => ['description' => 'Importer des budgets'],
        self::IMPORT_INVOICE => ['description' => 'Importer une facture'],
        self::IMPORT_SCOPE => ['description' => 'Importer un périmètre'],
        self::IMPORT_FILE => ['description' => 'Télécharger un document d\'import'],
        self::IMPORT_REPORT_VIEW => ['description' => 'Voir les rapports d\'import'],
        self::DASHBOARD_VIEW => ['description' => 'Voir le dashboard'],
        self::PRICING_IMPORT => ['description' => 'Importer un tarif']
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups("default")
     */
    private int $id;

    /**
     * @var Collection<Role>
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="permissions")
     */
    private Collection $roles;

    /**
     * @ORM\Column(type="enumTypePermissionCode", length=255, unique=true)
     *
     * @Groups("default")
     */
    private string $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups("default")
     */
    private ?string $description;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return ArrayCollection<Role>
     */
    public function getRoles(): ArrayCollection
    {
        return new ArrayCollection($this->roles->getValues());
    }

    public function setRoles(Collection $roles): void
    {
        foreach ($this->roles as $role) {
            if (!$roles->contains($role)) {
                $this->roles->removeElement($role);
            }
        }
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    public function addRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            return;
        }
        $this->roles->add($role);
    }

    public function removeRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            return;
        }
        $this->roles->removeElement($role);
    }
}
