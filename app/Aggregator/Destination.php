<?php

namespace App\Aggregator;

interface Destination
{
    public function send(): void;
}