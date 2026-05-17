<?php

/*
|--------------------------------------------------------------------------
| Configuração global do Pest para o FlowERP.
|
| Define o TestCase base e aplica o trait RefreshDatabase em
| todos os testes Feature, garantindo isolamento completo
| entre cada caso de teste (banco limpo a cada execucao).
|--------------------------------------------------------------------------
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
