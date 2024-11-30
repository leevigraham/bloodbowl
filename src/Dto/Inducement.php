<?php

namespace App\Dto;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Inducement
{
    public function __construct(
        public string $qty,
        public string $name,
        public string $cost,
        public string $descriptionHtml
    )
    {
    }

    public function getSlug($prefix = "inducement-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }
}