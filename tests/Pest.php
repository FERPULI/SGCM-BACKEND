<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

pest()->extend(TestCase::class)
     ->use(RefreshDatabase::class)
     ->use(WithFaker::class)
     ->in('Feature');
