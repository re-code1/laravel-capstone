<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Every Feature test gets the Laravel app and a fresh in-memory database (see phpunit.xml).
pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
