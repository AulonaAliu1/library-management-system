<?php declare(strict_types=1);
require_once __DIR__ . '/../repositories/UserRepository.php';
// Users / members-profile and admin member list will build on this.
class UserService{
    private UserRepository $users;
    public function __construct(?UserRepository $users = null){
        $this->users = $users ?? new UserRepository();

    }
   /**
    * @reutrn array<int, array<string, mixed>>

    */
   public function getAllMembers(): array 
   {
    $users = $this->getAllUsers();
    return array_values(array_filter($users, static fn (array $u): bool => ($u['role'] ?? '') === 'member'));

   }
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers():array
    {
        $users = $this->users->all();
        if($users !==[]){
            return $users;
        }
        return require __DIR__ . '/../data/users-data.php';
    }
   
}