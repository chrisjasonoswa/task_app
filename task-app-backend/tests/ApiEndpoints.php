<?php

namespace Tests;

class ApiEndpoints
{
    // ==== Base API ====
    public const BASE_API = '/api';

    // ==== Auth ====
    public const AUTH = self::BASE_API . '/auth';
    public const AUTH_LOGIN = self::AUTH . '/login';
    public const AUTH_REGISTER = self::AUTH . '/register';

    // ==== Tasks ====
    public const TASKS = self::BASE_API . '/tasks';
    public const TASKS_ORDER = self::TASKS . '/order';
    public const TASKS_DATES = self::TASKS . '/dates';
}
