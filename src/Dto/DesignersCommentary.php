<?php

namespace App\Dto;

class DesignersCommentary
{
    public function __construct(
        public string $source,
        public string $documentRef,
        public int $pageRef,
        public string $question,
        public string $answer,
        public ?string $team = null,
        public ?string $starPlayer = null,
        public ?array $skillsAndTraits = [],
    ) {
    }
}