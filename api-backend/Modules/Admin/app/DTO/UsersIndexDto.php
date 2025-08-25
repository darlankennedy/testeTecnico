<?php

namespace Modules\Admin\DTO;

final readonly class UsersIndexDto
{
    public function __construct(
        public int $perPage,
        public ?string $search,
        public string $orderBy,
        public string $direction,
        /** @var string[] */
        public array $with,
        /** @var array<string, mixed> */
        public array $filters,
    ) {}
}
