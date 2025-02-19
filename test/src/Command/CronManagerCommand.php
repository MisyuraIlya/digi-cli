<?php

namespace App\Command;

use App\Cron\Core\GetAgents;
use App\Cron\Core\GetBonuses;
use App\Cron\Core\GetCategories;
use App\Cron\Core\GetAttributes;
use App\Cron\Core\GetMigvans;
use App\Cron\Core\GetPacks;
use App\Cron\Core\GetPriceList;
use App\Cron\Core\GetPriceListDetailed;
use App\Cron\Core\GetPriceListUser;
use App\Cron\Core\GetProductPacks;
use App\Cron\Core\GetProducts;
use App\Cron\Core\GetStocks;
use App\Cron\Core\GetUsers;
use App\Entity\HomeEdit;
use App\Entity\User;
use App\Enum\UsersTypes;
use App\Repository\HomeEditRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[AsCommand(
    name: 'CronManager',
    description: 'Add a short description for your command',
)]
class CronManagerCommand extends Command
{
    private bool $isOnlinePrice;
    private bool $isOnlineMigvan;
    private bool $isUsedMigvan;

    public function __construct(
        private readonly GetUsers                    $users,
        private readonly GetAgents                   $agents,
        private readonly GetCategories               $categories,
        private readonly GetProducts                 $products,
        private readonly GetPriceList                $priceList,
        private readonly GetPriceListDetailed        $priceListDetailed,
        private readonly GetPriceListUser            $priceListUser,
        private readonly GetPacks                    $packs,
        private readonly GetProductPacks             $productPacks,
        private readonly GetStocks                   $stocks,
        private readonly GetAttributes               $getAttributes,
        private readonly GetMigvans                  $migvans,
        private readonly UserRepository              $userRepository,
        private readonly GetBonuses                  $bonuses,
        private readonly HomeEditRepository          $homeEditRepository,
        private readonly UserPasswordHasherInterface $hasher
    )
    {
        $this->testExtId =  $_ENV['TEST_USER'];
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

   private function Initialization()
   {
       $adminFind = $this->userRepository->findOneBy(['extId' => '48a147e2-29b5-46e1-928c-dbb8d9d2e207']);
       if(!$adminFind){
           $admin = new User();
           $admin->setName('admin');
           $admin->setCreatedAt(new \DateTimeImmutable());
           $admin->setUpdatedAt(new \DateTimeImmutable());
           $admin->setIsBlocked(false);
           $admin->setRoles(UsersTypes::ADMIN);
           $admin->setRole(UsersTypes::ADMIN);
           $admin->setRecovery(random_int(900000,999999));
           $admin->setIsVatEnabled(true);
           $admin->setEmail('admin@gmail.com');
           $admin->setIsAgent(false);
           $admin->setPhone('');
           $admin->setIsRegistered(true);
           $admin->setExtId('48a147e2-29b5-46e1-928c-dbb8d9d2e207');
           $admin->setPasswordUnencrypted('123456');
           $admin->setIsAllowAllClients(false);
           $admin->setIsAllowOrder(false);
           $admin->setPassword($this->hasher->hashPassword($admin,'123456'));
           $this->userRepository->createUser($admin,true);
       }
       $agentFind = $this->userRepository->findOneBy(['extId' => '2cca2cd6-2d37-4809-9520-cae76474113e']);
       if(!$agentFind){
           $agent = new User();
           $agent->setName('agent');
           $agent->setCreatedAt(new \DateTimeImmutable());
           $agent->setUpdatedAt(new \DateTimeImmutable());
           $agent->setIsAgent(false);
           $agent->setIsBlocked(false);
           $agent->setRoles(UsersTypes::SUPER_AGENT);
           $agent->setIsVatEnabled(true);
           $agent->setRole(UsersTypes::SUPER_AGENT);
           $agent->setRecovery(random_int(900000,999999));
           $agent->setEmail('agent@gmail.com');
           $agent->setPhone('');
           $agent->setIsRegistered(true);
           $agent->setExtId('2cca2cd6-2d37-4809-9520-cae76474113e');
           $agent->setPasswordUnencrypted('123456');
           $agent->setIsAllowAllClients(false);
           $agent->setIsAllowOrder(false);
           $agent->setPassword($this->hasher->hashPassword($agent,'123456'));
           $this->userRepository->createUser($agent,true);
       }

//       $userFind = $this->userRepository->findOneByExtId($this->testExtId);
//       if(!$userFind){
//           $test = new User();
//           $test->setName('test');
//           $test->setCreatedAt(new \DateTimeImmutable());
//           $test->setUpdatedAt(new \DateTimeImmutable());
//           $test->setIsBlocked(false);
//           $test->setRoles(UsersTypes::USER);
//           $test->setRole(UsersTypes::USER);
//           $test->setIsAgent(false);
//           $test->setRecovery(random_int(900000,999999));
//           $test->setEmail('test@gmail.com');
//           $test->setPhone('');
//           $test->setIsRegistered(true);
//           $test->setExtId($this->testExtId);
//           $test->setPasswordUnencrypted('123456');
//           $test->setIsAllowAllClients(false);
//           $test->setIsAllowOrder(false);
//           $test->setPassword($this->hasher->hashPassword($test,'123456'));
//           $this->userRepository->createUser($test,true);
//       }


       $data = [
           [
               'type' => 'main',
               'orden' => 0,
               'is_video' => 0,
               'is_banner' => 1,
               'is_active' => 1,
               'count' => 1,
               'count_mobile' => NULL,
               'is_pop_up' => 0,
               'is_deletable' => 0
           ],
           [
               'type' => 'categories',
               'orden' => 1,
               'is_video' => 0,
               'is_banner' => 1,
               'is_active' => 1,
               'count' => 4,
               'count_mobile' => 2,
               'is_pop_up' => 0,
               'is_deletable' => 0
           ],
           [
               'type' => 'productsNew',
               'orden' => 3,
               'is_video' => 0,
               'is_banner' => 1,
               'is_active' => 1,
               'count' => 4,
               'count_mobile' => 2,
               'is_pop_up' => 0,
               'is_deletable' => 0
           ],
           [
               'type' => 'productsSale',
               'orden' => 2,
               'is_video' => 0,
               'is_banner' => 1,
               'is_active' => 1,
               'count' => 4,
               'count_mobile' => 2,
               'is_pop_up' => 0,
               'is_deletable' => 0
           ],
           [
               'type' => 'logos',
               'orden' => 4,
               'is_video' => 0,
               'is_banner' => 1,
               'is_active' => 1,
               'count' => 4,
               'count_mobile' => NULL,
               'is_pop_up' => 0,
               'is_deletable' => 0
           ]
       ];

       foreach ($data as $item) {
           $findItem = $this->homeEditRepository->findOneBy(['type' => $item['type']]);
           if(!$findItem){
               $home = new HomeEdit();
               $home->setType($item['type']);
               $home->setOrden($item['orden']);
               $home->setIsActive($item['is_active']);
               $home->setCount($item['count']);
               $home->setCountMobile($item['count_mobile']);
               $home->setIsVideo($item['is_video']);
               $home->setIsBanner($item['is_banner']);
               $home->setCount($item['count']);
               $home->setCountMobile($item['count_mobile']);
               $home->setIsPopup($item['is_pop_up']);
               $home->setIsDeletable($item['is_deletable']);
               $this->homeEditRepository->save($home,true);
           }
       }
   }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

//        $this->Initialization();
//        $this->agents->sync();
//        $this->priceList->sync();
//        $this->users->sync();
//        $this->priceListUser->sync();
//        $this->categories->sync();
//        $this->products->sync();
//        $this->getAttributes->sync();
//        $this->priceListDetailed->sync();
//         $this->packs->sync();;
//         $this->productPacks->sync();
//         $this->stocks->sync();
//         if(!$this->isOnlineMigvan && $this->isUsedMigvan){
//             $this->migvans->sync();
//         }
// $this->bonuses->sync();
        $io->success('All Cron Function Executed');
        return Command::SUCCESS;
    }
}
