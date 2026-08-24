<?php

namespace App\Platform;

use Illuminate\Support\Collection;

final class ConceptRegistry
{
    /** @var array<string, ConceptRegistration> */
    private array $concepts = [];

    public function register(ConceptRegistration $concept): void
    {
        $this->concepts[$concept->slug] = $concept;
    }

    public function has(string $slug): bool
    {
        return array_key_exists($slug, $this->concepts);
    }

    /** @return Collection<int, ConceptRegistration> */
    public function all(): Collection
    {
        return collect($this->concepts)->values();
    }

    /** @return Collection<string, Collection<int, ConceptRegistration>> */
    public function groupedByCategory(): Collection
    {
        return $this->all()->groupBy(fn (ConceptRegistration $concept) => $concept->category);
    }
}
