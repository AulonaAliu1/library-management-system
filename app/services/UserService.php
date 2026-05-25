<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

class UserService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllMembers(): array
    {
        return $this->users->members();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers(): array
    {
        return $this->users->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMemberById(int $id): ?array
    {
        $member = $this->users->findMemberById($id);

        if ($member === null) {
            return null;
        }

        unset($member['password']);

        return $member;
    }

    /**
     * @return array{success: bool, errors: array<string, string>, member: array<string, string>}
     */
    public function createMember(array $input): array
    {
        $member = $this->normalizeMemberInput($input);
        $errors = $this->validateMemberData($member, true);

        if ($errors === [] && ! $this->users->create($member)) {
            $errors['general'] = 'Unable to create the member in the database.';
        }

        return [
            'success' => $errors === [],
            'errors' => $errors,
            'member' => $member,
        ];
    }

    /**
     * @return array{success: bool, errors: array<string, string>, member: array<string, string>}
     */
    public function updateMember(int $id, array $input): array
    {
        $existingMember = $this->users->findMemberById($id);
        $member = $this->normalizeMemberInput($input);

        if ($existingMember === null) {
            return [
                'success' => false,
                'errors' => ['general' => 'Member not found.'],
                'member' => $member,
            ];
        }

        $errors = $this->validateMemberData($member, false, $id);

        if ($errors === [] && ! $this->users->updateMember($id, $member)) {
            $errors['general'] = 'Unable to update the member in the database.';
        }

        return [
            'success' => $errors === [],
            'errors' => $errors,
            'member' => $member,
        ];
    }

    public function deleteMember(int $id): bool
    {
        $member = $this->users->findMemberById($id);

        if ($member === null) {
            return false;
        }

        return $this->users->delete($id);
    }

    /**
     * @return array<string, string>
     */
    private function normalizeMemberInput(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'username' => trim((string) ($input['username'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'role' => 'member',
            'password' => (string) ($input['password'] ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validateMemberData(array $member, bool $requirePassword, ?int $exceptId = null): array
    {
        $errors = [];

        if (! is_filled($member['name'])) {
            $errors['name'] = 'Name is required.';
        }

        if (! is_filled($member['username'])) {
            $errors['username'] = 'Username is required.';
        } elseif (! is_valid_username($member['username'])) {
            $errors['username'] = 'Username must start with a letter and contain only letters, numbers, or underscores.';
        } elseif ($this->users->usernameExists($member['username'], $exceptId)) {
            $errors['username'] = 'This username is already in use.';
        }

        if (! is_filled($member['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (! is_valid_email($member['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($this->users->emailExists($member['email'], $exceptId)) {
            $errors['email'] = 'This email is already in use.';
        }

        if (! is_valid_role($member['role']) || $member['role'] !== 'member') {
            $errors['role'] = 'Only member accounts can be managed from this page.';
        }

        $password = $member['password'];

        if ($requirePassword && ! is_filled($password)) {
            $errors['password'] = 'Password is required.';
        } elseif ($password !== '' && ! is_strong_enough_password($password)) {
            $errors['password'] = 'Password must contain at least 8 characters.';
        }

        return $errors;
    }
}