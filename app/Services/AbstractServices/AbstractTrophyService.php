<?php

namespace App\Services\AbstractServices;

use App\Models\Trophy;
use App\Repositories\RepositoryInterface;
use App\Services\FileService;
use App\Web3\Pinata;
use App\Web3\TrophyNFT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AbstractTrophyService
{
    protected FileService $fileService;
    protected RepositoryInterface $trophyRepository;
    protected TrophyNFT $trophyNFT;
    protected Pinata $pinata;

    public function __construct(RepositoryInterface $trophyRepository, TrophyNFT $trophyNFT, Pinata $pinata)
    {
        $this->fileService = new FileService();
        $this->trophyRepository = $trophyRepository;
        $this->pinata = $pinata;
        $this->trophyNFT = $trophyNFT;
    }

    public function remove($id)
    {
        return $this->trophyRepository->remove($id);
    }

    public function getTrophy($id)
    {
        $trophy = $this->trophyRepository->find($id);
        $trophy->badges = $trophy->badges;
        return $trophy;
    }

    public function store($data){
        try {
            DB::beginTransaction();

                if (isset($data['is_key'])) {
                    if (boolval($data['is_key'] && isset($data['key']))){
                        $data['key_id'] = $data['key'];
                    }
                }

                $data['is_nft'] = boolval($data['is_nft'] ?? false);
                $data['image'] = $this->fileService->saveTrophyImage($data['image']);
                if ($data['image']){
                    $trophy = $this->trophyRepository->create($data);
                    $trophy->badges()->attach($data['badges']);
                    if ($data['is_nft']) {
                        $metadata = $trophy->jsonSerialize();
                        $metadata['image'] = $this->pinata->pinFileToIPFS(storage_path(Trophy::FILE_PATH . '/' . $data['image']));
                        $url = $this->pinata->pinJSONToIPFS($metadata);
                        $this->trophyNFT->create(pinataUrl: $url, categoryId: $trophy->id, maxSupply: $data['max_supply'] ?? 10);
                    }
                }else{
                    return null;
                }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
                Log::info('AbstractTrophyService@create: ' . $e->getMessage());
                return null;
        }
        return $trophy;
    }

    public function update($data, $id){
        try {
            DB::beginTransaction();
            if (isset($data['image'])){
                $data['image'] = $this->fileService->saveTrophyImage($data['image']);
            }

            if (isset($data['is_key']) && boolval($data['is_key'] && isset($data['key']))){
                $data['key_id'] = $data['key'];
            } else {
                $data['key_id'] = null;
            }

            $this->trophyRepository->update($id, $data);
            $trophy = $this->trophyRepository->find($id);
            $trophy->badges()->sync($data['badges']);

            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            Log::info('AbstractTrophyService@create: ' . $e->getMessage());
            return null;
        }
        return $trophy;
    }



}
