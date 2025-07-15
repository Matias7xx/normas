<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class StorageHelper
{
    /**
     * Obter instância do Storage com bucket específico
     */
    public static function bucket($bucketName)
    {
        // cópia da configuração do s3
        $config = config('filesystems.disks.s3');
        
        // Alterar apenas o bucket
        $config['bucket'] = $bucketName;
        
        // Criar configuração temporária
        Config::set('filesystems.disks.s3_temp', $config);
        
        // Retorna instância do Storage com o bucket específico
        return Storage::disk('s3_temp');
    }
    
    /**
     * = bucket de funcionais
     */
    public static function fotos()
    {
        return self::bucket(env('AWS_BUCKET_FOTOS', 'funcionais'));
    }
    
    /**
     *  bucket de normas
     */
    public static function normas()
    {
        return self::bucket(env('AWS_BUCKET_NORMAS', 'normas'));
    }
}