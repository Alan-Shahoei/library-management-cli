<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json;

use AlanShahoei\LibraryManagement\Domain\Member;
use AlanShahoei\LibraryManagement\Domain\Repository\MemberRepositoryInterface;
use RuntimeException;

class JsonMemberRepository implements MemberRepositoryInterface
{
    public function __construct(private JsonFileStorage $storage)
    {
    }

    public function save(Member $member): void
    {
        $members = $this->findAll();
        $isUpdated = false;

        foreach ($members as $key => $existingMember) {
            if ($existingMember->getId() === $member->getId()) {
                $members[$key] = $member;
                $isUpdated = true;
                break;
            }
        }

        if (!$isUpdated) {
            $members[] = $member;
        }

        $records = [];

        foreach ($members as $existingMember) {
            $records[] = $this->mapToRecord($existingMember);
        }

        $this->storage->write($records);
    }

    public function findById(string $id): ?Member
    {
        $members = $this->findAll();

        foreach ($members as $member) {
            if ($member->getId() === $id) {
                return $member;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        $records = $this->storage->read();
        $members = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new RuntimeException('Invalid member record in JSON storage.');
            }

            $members[] = $this->mapToMember($record);
        }

        return $members;
    }

    private function mapToMember(array $record): Member
    {
        if (
            !isset($record['id'], $record['fullName'], $record['phoneNumber'], $record['isActive'])
            || !is_string($record['id'])
            || !is_string($record['fullName'])
            || !is_string($record['phoneNumber'])
            || !is_bool($record['isActive'])
        ) {
            throw new RuntimeException('Invalid member record in JSON storage.');
        }

        return new Member($record['id'], $record['fullName'], $record['phoneNumber'], $record['isActive']);
    }

    private function mapToRecord(Member $member): array
    {
        return [
            'id' => $member->getId(),
            'fullName' => $member->getFullName(),
            'phoneNumber' => $member->getPhoneNumber(),
            'isActive' => $member->isActive(),
        ];
    }
}