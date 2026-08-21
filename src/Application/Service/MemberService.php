<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Application\Service;

use AlanShahoei\LibraryManagement\Domain\Member;
use AlanShahoei\LibraryManagement\Domain\Repository\LoanRepositoryInterface;
use AlanShahoei\LibraryManagement\Domain\Repository\MemberRepositoryInterface;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class MemberService
{
    public function __construct(
        private LoanRepositoryInterface $loanRepository,
        private MemberRepositoryInterface $memberRepository
    ) {
    }

    public function registerMember(string $fullName, string $phoneNumber): Member
    {
        $members = $this->memberRepository->findAll();
        $id = $this->generateMemberId($members);
        $member = new Member($id, $fullName, $phoneNumber);

        $this->memberRepository->save($member);

        return $member;
    }

    public function updateMember(string $id, string $fullName, string $phoneNumber): Member
    {
        $member = $this->findMemberOrFail($id);

        $member->updateDetails($fullName, $phoneNumber);

        $this->memberRepository->save($member);

        return $member;
    }

    public function activateMember(string $id): Member
    {
        $member = $this->findMemberOrFail($id);

        $member->activate();

        $this->memberRepository->save($member);

        return $member;
    }

    public function deactivateMember(string $id): Member
    {
        $member = $this->findMemberOrFail($id);

        $activeLoans = $this->loanRepository->findActiveByMemberId(
            $member->getId()
        );

        if ($activeLoans !== []) {
            throw new LogicException('Member cannot be deactivated while they have active loans.');
        }

        $member->deactivate();

        $this->memberRepository->save($member);

        return $member;
    }

    public function getAllMembers(): array
    {
        return $this->memberRepository->findAll();
    }

    private function generateMemberId(array $members): string
    {
        $max = 0;

        foreach ($members as $member) {
            $numericPart = (int) substr($member->getId(), 1);

            if ($numericPart > $max) {
                $max = $numericPart;
            }
        }

        return 'M' . sprintf('%05d', $max + 1);
    }

    private function findMemberOrFail(string $id): Member
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            throw new InvalidArgumentException('Member ID cannot be empty.');
        }

        $member = $this->memberRepository->findById($normalizedId);

        if ($member === null) {
            throw new RuntimeException("Member not found: {$normalizedId}");
        }

        return $member;
    }
}
