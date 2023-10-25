<?php

declare(strict_types=1);

namespace App\Request;

class Pagination
{
    protected int $page;
    protected int $perPage;
    protected ?string $sort;
    protected ?string $sortOrder;

    public function __construct(int $page = 1, int $perPage = 25, ?string $sort = null, ?string $sortOrder = 'ASC')
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->sort = empty($sort) ? null : strtolower($sort);
        $this->sortOrder = empty($sortOrder) ? null : strtolower($sortOrder);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }
}