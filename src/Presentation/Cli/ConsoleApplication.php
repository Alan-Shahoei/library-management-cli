<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Presentation\Cli;

use AlanShahoei\LibraryManagement\Application\Service\BookService;
use AlanShahoei\LibraryManagement\Application\Service\LoanService;
use AlanShahoei\LibraryManagement\Application\Service\MemberService;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class ConsoleApplication
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private BookService $bookService,
        private MemberService $memberService,
        private LoanService $loanService
    ) {
    }

    public function run(): void
    {
        $this->runMenu('Library Management', [
            '1' => ['label' => 'Book management', 'handler' => [$this, 'showBookMenu']],
            '2' => ['label' => 'Member management', 'handler' => [$this, 'showMemberMenu']],
            '3' => ['label' => 'Loan management', 'handler' => [$this, 'showLoanMenu']],
        ], 'Exit');
    }

    private function showBookMenu(): void
    {
        $this->runMenu('Book Management', [
            '1' => ['label' => 'Add book', 'handler' => [$this, 'handleAddBook']],
            '2' => ['label' => 'Update book', 'handler' => [$this, 'handleUpdateBook']],
            '3' => ['label' => 'Add book copy', 'handler' => [$this, 'handleAddBookCopy']],
            '4' => ['label' => 'Activate book copy', 'handler' => [$this, 'handleActivateBookCopy']],
            '5' => ['label' => 'Deactivate book copy', 'handler' => [$this, 'handleDeactivateBookCopy']],
            '6' => ['label' => 'List books', 'handler' => [$this, 'handleListBooks']],
            '7' => ['label' => 'List book copies', 'handler' => [$this, 'handleListBookCopies']],
        ], 'Back');
    }

    private function showMemberMenu(): void
    {
        $this->runMenu('Member Management', [
            '1' => ['label' => 'Register member', 'handler' => [$this, 'handleRegisterMember']],
            '2' => ['label' => 'Update member', 'handler' => [$this, 'handleUpdateMember']],
            '3' => ['label' => 'Activate member', 'handler' => [$this, 'handleActivateMember']],
            '4' => ['label' => 'Deactivate member', 'handler' => [$this, 'handleDeactivateMember']],
            '5' => ['label' => 'List members', 'handler' => [$this, 'handleListMembers']],
        ], 'Back');
    }

    private function showLoanMenu(): void
    {
        $this->runMenu('Loan Management', [
            '1' => ['label' => 'Borrow book', 'handler' => [$this, 'handleBorrowBook']],
            '2' => ['label' => 'Return book', 'handler' => [$this, 'handleReturnBook']],
            '3' => ['label' => 'List all loans', 'handler' => [$this, 'handleListAllLoans']],
            '4' => ['label' => 'List active loans', 'handler' => [$this, 'handleListActiveLoans']],
            '5' => ['label' => 'List overdue loans', 'handler' => [$this, 'handleListOverdueLoans']],
            '6' => ['label' => 'List member loans', 'handler' => [$this, 'handleListMemberLoans']],
        ], 'Back');
    }

    private function runMenu(string $title, array $actions, string $zeroOptionLabel): void
    {
        while (true) {
            echo PHP_EOL . "=== {$title} ===" . PHP_EOL;

            foreach ($actions as $option => $action) {
                echo "{$option}. {$action['label']}" . PHP_EOL;
            }

            echo "0. {$zeroOptionLabel}" . PHP_EOL;

            $selectedOption = $this->prompt('Select an option: ');

            if ($selectedOption === '0') {
                return;
            }

            if (!isset($actions[$selectedOption])) {
                echo 'Invalid option.' . PHP_EOL;

                continue;
            }

            try {
                $handler = $actions[$selectedOption]['handler'];
                $handler();
            } catch (Throwable $exception) {
                echo "Error: {$exception->getMessage()}" . PHP_EOL;
            }
        }
    }

    private function handleAddBook(): void
    {
        $title = $this->prompt('Title: ');
        $authors = $this->readAuthors();
        $edition = $this->promptOptional('Edition (optional): ');

        $book = $this->bookService->addBook($title, $authors, $edition);

        echo "Book added successfully. ID: {$book->getId()}" . PHP_EOL;
    }

    private function handleUpdateBook(): void
    {
        $id = $this->prompt('Book ID: ');
        $title = $this->prompt('Title: ');
        $authors = $this->readAuthors();
        $edition = $this->promptOptional('Edition (optional): ');

        $book = $this->bookService->updateBook($id, $title, $authors, $edition);

        echo "Book updated successfully. ID: {$book->getId()}" . PHP_EOL;
    }

    private function handleAddBookCopy(): void
    {
        $bookId = $this->prompt('Book ID: ');
        $barcode = $this->prompt('Barcode: ');

        $bookCopy = $this->bookService->addBookCopy($bookId, $barcode);

        echo "Book copy added successfully. Barcode: {$bookCopy->getBarcode()}" . PHP_EOL;
    }

    private function handleActivateBookCopy(): void
    {
        $barcode = $this->prompt('Barcode: ');
        $bookCopy = $this->bookService->activateBookCopy($barcode);

        echo "Book copy activated successfully. Barcode: {$bookCopy->getBarcode()}" . PHP_EOL;
    }

    private function handleDeactivateBookCopy(): void
    {
        $barcode = $this->prompt('Barcode: ');
        $bookCopy = $this->bookService->deactivateBookCopy($barcode);

        echo "Book copy deactivated successfully. Barcode: {$bookCopy->getBarcode()}" . PHP_EOL;
    }

    private function handleListBooks(): void
    {
        $books = $this->bookService->getAllBooks();

        if ($books === []) {
            echo 'No books found.' . PHP_EOL;

            return;
        }

        foreach ($books as $book) {
            $authors = implode(', ', $book->getAuthors());
            $edition = $book->getEdition() ?? '-';

            echo "{$book->getId()} | {$book->getTitle()} | {$authors} | {$edition}" . PHP_EOL;
        }
    }

    private function handleListBookCopies(): void
    {
        $bookId = $this->prompt('Book ID: ');
        $bookCopies = $this->bookService->getBookCopies($bookId);

        if ($bookCopies === []) {
            echo 'No book copies found.' . PHP_EOL;

            return;
        }

        foreach ($bookCopies as $bookCopy) {
            $status = $bookCopy->isActive() ? 'Active' : 'Inactive';

            echo "{$bookCopy->getBarcode()} | {$status}" . PHP_EOL;
        }
    }

    private function handleRegisterMember(): void
    {
        $fullName = $this->prompt('Full name: ');
        $phoneNumber = $this->prompt('Phone number: ');

        $member = $this->memberService->registerMember($fullName, $phoneNumber);

        echo "Member registered successfully. ID: {$member->getId()}" . PHP_EOL;
    }

    private function handleUpdateMember(): void
    {
        $id = $this->prompt('Member ID: ');
        $fullName = $this->prompt('Full name: ');
        $phoneNumber = $this->prompt('Phone number: ');

        $member = $this->memberService->updateMember($id, $fullName, $phoneNumber);

        echo "Member updated successfully. ID: {$member->getId()}" . PHP_EOL;
    }

    private function handleActivateMember(): void
    {
        $id = $this->prompt('Member ID: ');
        $member = $this->memberService->activateMember($id);

        echo "Member activated successfully. ID: {$member->getId()}" . PHP_EOL;
    }

    private function handleDeactivateMember(): void
    {
        $id = $this->prompt('Member ID: ');
        $member = $this->memberService->deactivateMember($id);

        echo "Member deactivated successfully. ID: {$member->getId()}" . PHP_EOL;
    }

    private function handleListMembers(): void
    {
        $members = $this->memberService->getAllMembers();

        if ($members === []) {
            echo 'No members found.' . PHP_EOL;

            return;
        }

        foreach ($members as $member) {
            $status = $member->isActive() ? 'Active' : 'Inactive';

            echo "{$member->getId()} | {$member->getFullName()} | {$member->getPhoneNumber()} | {$status}" . PHP_EOL;
        }
    }

    private function handleBorrowBook(): void
    {
        $barcode = $this->prompt('Book copy barcode: ');
        $memberId = $this->prompt('Member ID: ');

        $loan = $this->loanService->borrowBook($barcode, $memberId, new DateTimeImmutable());
        $dueAt = $loan->getDueAt()->format(self::DATE_FORMAT);

        echo "Book borrowed successfully. Due at: {$dueAt}" . PHP_EOL;
    }

    private function handleReturnBook(): void
    {
        $barcode = $this->prompt('Book copy barcode: ');

        $loan = $this->loanService->returnBook($barcode, new DateTimeImmutable());
        $returnedAt = $loan->getReturnedAt()?->format(self::DATE_FORMAT) ?? '-';

        echo "Book returned successfully. Returned at: {$returnedAt}" . PHP_EOL;
    }

    private function handleListAllLoans(): void
    {
        $this->displayLoans($this->loanService->getAllLoans());
    }

    private function handleListActiveLoans(): void
    {
        $this->displayLoans($this->loanService->getActiveLoans());
    }

    private function handleListOverdueLoans(): void
    {
        $this->displayLoans($this->loanService->getOverdueLoans(new DateTimeImmutable()));
    }

    private function handleListMemberLoans(): void
    {
        $memberId = $this->prompt('Member ID: ');
        $this->displayLoans($this->loanService->getLoansByMemberId($memberId));
    }

    private function displayLoans(array $loans): void
    {
        if ($loans === []) {
            echo 'No loans found.' . PHP_EOL;

            return;
        }

        foreach ($loans as $loan) {
            $status = $loan->isActive() ? 'Active' : 'Returned';
            $borrowedAt = $loan->getBorrowedAt()->format(self::DATE_FORMAT);
            $dueAt = $loan->getDueAt()->format(self::DATE_FORMAT);
            $returnedAt = $loan->getReturnedAt()?->format(self::DATE_FORMAT) ?? '-';

            echo "Copy: {$loan->getBookCopyBarcode()} | Member: {$loan->getMemberId()} | Status: {$status}" . PHP_EOL;
            echo "Borrowed: {$borrowedAt} | Due: {$dueAt} | Returned: {$returnedAt}" . PHP_EOL;
        }
    }

    private function readAuthors(): array
    {
        $authors = $this->prompt('Authors (comma-separated): ');

        return array_map('trim', explode(',', $authors));
    }

    private function promptOptional(string $message): ?string
    {
        $value = $this->prompt($message);

        return $value === '' ? null : $value;
    }

    private function prompt(string $message): string
    {
        echo $message;

        $input = fgets(STDIN);

        if ($input === false) {
            throw new RuntimeException('Unable to read input.');
        }

        return trim($input);
    }
}
