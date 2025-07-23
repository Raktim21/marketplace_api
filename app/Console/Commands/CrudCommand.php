<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:crud {name} {--model=} {--cpath=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $modelName = $this->option('model');
        $controllerPath = $this->option('cpath');


        //Create Model
        $filePath = app_path("Models/$modelName".'.php');
        if (File::exists($filePath)) {
            $this->error('Model already exists!');
            return 1;
        }
        $this->call('make:model', ['name' => $modelName, '--migration' => true]);
        $stubModel = File::get(resource_path('stubs/model.stub'));
        $stubModelContent = str_replace('{{modelName}}', $modelName, $stubModel);
        File::put($filePath, $stubModelContent);




        // //Create Service
        // $fileName = ucfirst($name) . 'Service.php';
        // $filePath = app_path("Http/Services/$fileName");
        // if (File::exists($filePath)) {
        //     $this->error('Service already exists!');
        //     return 1;
        // }
        // $stubService = File::get(resource_path('stubs/service.stub'));
        // $stubServiceContent = str_replace('{{modelName}}', $modelName, $stubService);
        // File::put($filePath, $stubServiceContent);




        // //Create request create
        // $filePath = app_path("Http/Requests/$modelName/$modelName".'CreateRequest.php');
        // if (File::exists($filePath)) {
        //     $this->error('Request already exists!');
        //     return 1;
        // }
        // $this->call('make:Request', ['name' => "$modelName/$modelName"."CreateRequest"]);
        // $stubCreateRequest = File::get(resource_path('stubs/createRequest.stub'));
        // $stubCreateRequestContent  = str_replace('{{modelName}}', $modelName, $stubCreateRequest);
        // File::put($filePath, $stubCreateRequestContent);




        // //Update request create
        // $filePath = app_path("Http/Requests/$modelName/$modelName".'UpdateRequest.php');
        // if (File::exists($filePath)) {
        //     $this->error('Request already exists!');
        //     return 1;
        // }
        // $this->call('make:Request', ['name' => "$modelName/$modelName"."UpdateRequest"]);
        // $stubUpdateRequest = File::get(resource_path('stubs/updateRequest.stub'));
        // $stubUpdateRequestContent = str_replace('{{modelName}}', $modelName, $stubUpdateRequest);
        // File::put($filePath, $stubUpdateRequestContent);




        //Create Controller
        if ($controllerPath == null) {
            $filePath = app_path("Http/Controllers/$modelName/$modelName".'Controller.php');
            $controllerName = "$modelName/$modelName";
            $namespace = "App\Http\Controllers\\$modelName";
        }else {
            $filePath = app_path("Http/Controllers/$controllerPath/$modelName".'Controller.php');
            $controllerName = "$controllerPath/$modelName";
            $namespace = "App\Http\Controllers\\$controllerPath";
        }

        if (File::exists($filePath)) {
            $this->error('Controller already exists!');
            return 1;
        }

        $this->call('make:controller', ['name' => $controllerName.'Controller']);

        $stubController = File::get(resource_path('stubs/controller.stub'));
        
        $string = ['{{nameSpace}}', '{{modelName}}']; // Array of strings to search for
        $replace = [$namespace, $modelName];
        $stubControllerContent = str_replace($string, $replace, $stubController);

        File::put($filePath, $stubControllerContent);

        $this->info("Controller created successfully: $modelName");


        //Create index.blade.php
        $filePath = resource_path("views/tenant/dashboard/$modelName/index.blade.php");
        if (File::exists($filePath)) {
            $this->error('View already exists!');
            return 1;
        }
        $stubView = File::get(resource_path('stubs/view.stub'));
        $stubViewContent = str_replace('{{modelName}}', $modelName, $stubView);

        

        return 0;
    }
}
