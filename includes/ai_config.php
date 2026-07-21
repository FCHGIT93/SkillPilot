<?php

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$OPENAI_API_KEY = $_ENV["OPENAI_API_KEY"] ?? getenv("OPENAI_API_KEY");

if (!$OPENAI_API_KEY) {
    die("OpenAI API key is missing.");
}