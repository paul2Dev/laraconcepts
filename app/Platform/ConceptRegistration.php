<?php

namespace App\Platform;

final readonly class ConceptRegistration
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $category,
        public string $demoRoute,
    ) {}
}
