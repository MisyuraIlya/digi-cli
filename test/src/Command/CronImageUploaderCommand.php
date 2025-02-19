<?php

namespace App\Command;

use App\Entity\MediaObject;
use App\Entity\ProductImages;
use App\Erp\Core\ErpManager;
use App\Repository\MediaObjectRepository;
use App\Repository\ProductImagesRepository;
use App\Repository\ProductRepository;
use App\Service\FtpService;
use App\Service\ImageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'CronImageUploader',
    description: 'Add a short description for your command',
)]
class CronImageUploaderCommand extends Command
{
    private string $url = 'http://82.166.252.132:2222/images';
    private string $urlImage = 'http://82.166.252.132:2222/download/';
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductImagesRepository $productImagesRepository,
        private readonly MediaObjectRepository $mediaObjectRepository,
        private readonly ErpManager $erpManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $products = $this->productRepository->GetAllProducts();


        foreach ($products as $productRec){
            $img = $this->FindSkuImage($productRec['sku']);
            if($img){
                $this->fetchImageFromApi($img);
            }
        }
//        $sourceFolder = __DIR__.'/../../public/images';
//        $targetFolder = __DIR__.'/../../public/imagesResized';
//        $targetSizeBytes = 1024 * 1024; // 1 MB
//        (new ImageService())::resizeImagesInFolder($sourceFolder, $targetFolder, $targetSizeBytes);
        $this->SaveImagesInDb();
//        (new FtpService('digitrade.com.ua/src/img3/product', 'src/img3/product'))->uploadAllImagesFromResizedFolder();
//        $this->DeletAll();
        $io->success('Images Successfuly resied and updated and loaded to ftp');

        return Command::SUCCESS;
    }

    private function SaveImagesInDb()
    {
        $targetFolder = __DIR__.'/../../public/media/product';
        if (!is_dir($targetFolder)) {
            throw new \Exception("The 'product' folder does not exist locally.");
        }

        $localFiles = scandir($targetFolder);
        foreach ($localFiles as $file) {
            if ($file !== '.' && $file !== '..') {
                if (preg_match('/^(.+)\..+?$/', $file, $matches)) {
                    $fileName = $matches[1];
                    $product = $this->productRepository->findOneBySku($fileName);
                    $isExistMediaObject = $this->mediaObjectRepository->findOneByFilePath($file);
                    if($product && !$isExistMediaObject){
                        $newMedia = new MediaObject();
                        $newMedia->setSource('product');
                        $newMedia->setCreatedAt(new \DateTimeImmutable());
                        $newMedia->setFilePath($file);
                        $this->mediaObjectRepository->save($newMedia,true);

                        $newProductImage = new ProductImages();
                        $newProductImage->setProduct($product);
                        $newProductImage->setMediaObject($newMedia);
                        $this->productImagesRepository->save($newProductImage);

                        $product->setUpdatedAt(new \DateTimeImmutable());
                        $product->setDefaultImagePath($file);
                        $this->productRepository->createProduct($product,true);
                    }
                }
            }
        }
    }

    private function DeletAll()
    {
        $targetFolder = __DIR__.'/../../public/images';
        if (!is_dir($targetFolder)) {
            throw new \Exception("The 'images' folder does not exist locally.");
        }

        $files = glob($targetFolder . '/*'); // Get all file names in the folder
        foreach($files as $file) { // Iterate through each file
            if(is_file($file)) {
                unlink($file); // Delete the file
            }
        }

        $targetFolder = __DIR__.'/../../public/imagesResized';
        if (!is_dir($targetFolder)) {
            throw new \Exception("The 'imagesResized' folder does not exist locally.");
        }
        $files = glob($targetFolder . '/*'); // Get all file names in the folder
        foreach($files as $file) { // Iterate through each file
            if(is_file($file)) {
                unlink($file); // Delete the file
            }
        }

    }

    private function FindSkuImage(string $sku)
    {
        try {
            $url = "http://82.166.252.132:2222/images";
            $response = file_get_contents($url);
            if ($response === false) {
                return null;
            }
            $data = json_decode($response, true);
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if (strpos($image, $sku) !== false) {
                        return $image;
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            error_log('Error finding SKU image: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchImageFromApi(string $sku)
    {
        try {
            $url = "http://82.166.252.132:2222/download/" . $sku;
            $imageData = file_get_contents($url);

            if ($imageData) {
                $headers = get_headers($url, 1);
                $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'image/jpeg';
                $contentType = strtok($contentType, ';');
                $mimeTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/bmp' => 'bmp',
                    'image/webp' => 'webp',
                    'image/tiff' => 'tiff',
                ];
                $extension = $mimeTypes[$contentType] ?? 'jpg';
                file_put_contents("public/media/product/$sku", $imageData);
            }
        } catch (\Exception $e) {
            error_log('Error fetching image: ' . $e->getMessage());
        }
    }

}
