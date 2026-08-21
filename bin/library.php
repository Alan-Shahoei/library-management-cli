<?php

declare(strict_types=1);

use AlanShahoei\LibraryManagement\Application\Service\BookService;
use AlanShahoei\LibraryManagement\Application\Service\LoanService;
use AlanShahoei\LibraryManagement\Application\Service\MemberService;
use AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json\JsonBookCopyRepository;
use AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json\JsonBookRepository;
use AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json\JsonFileStorage;
use AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json\JsonLoanRepository;
use AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json\JsonMemberRepository;
use AlanShahoei\LibraryManagement\Presentation\Cli\ConsoleApplication;

try {
    $projectDirectory = dirname(__DIR__);

    require $projectDirectory . '/vendor/autoload.php';

    $bookStorage = new JsonFileStorage($projectDirectory . '/storage/books.json');
    $bookCopyStorage = new JsonFileStorage($projectDirectory . '/storage/book-copies.json');
    $memberStorage = new JsonFileStorage($projectDirectory . '/storage/members.json');
    $loanStorage = new JsonFileStorage($projectDirectory . '/storage/loans.json');

    $bookRepository = new JsonBookRepository($bookStorage);
    $bookCopyRepository = new JsonBookCopyRepository($bookCopyStorage);
    $memberRepository = new JsonMemberRepository($memberStorage);
    $loanRepository = new JsonLoanRepository($loanStorage);

    $bookService = new BookService($bookRepository, $bookCopyRepository, $loanRepository);
    $memberService = new MemberService($loanRepository, $memberRepository);
    $loanService = new LoanService($loanRepository, $memberRepository, $bookCopyRepository);

    $application = new ConsoleApplication($bookService, $memberService, $loanService);
    $application->run();
} catch (Throwable $exception) {
    fwrite(STDERR, "Fatal error: {$exception->getMessage()}" . PHP_EOL);

    exit(1);
}