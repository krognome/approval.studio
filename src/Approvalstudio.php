<?php

namespace Krognome\Approvalstudio;

use Cache;
use Http;
use Krognome\Approvalstudio\Exceptions\ApprovalstudioRequestFailedException;
use Krognome\Approvalstudio\Exceptions\ApprovalstudioTokenInvalidException;

class Approvalstudio
{
    protected $apiURL = "https://api.approval.studio/api/v1/";
    protected $tokenLoginPath = "token/login";
    protected $tokenLifeSeconds = 7200; // The token is valid for a limited amount of time, 120 minutes by default. When expired and still used you will have response HTTP code 401, Unauthorized
    public $username;
    public $password;

    public function __construct($username, $password){
        $this->username = $username;
        $this->password = $password;
    }

    public function getToken(){
        if (Cache::has('APPROVALSTUDIO_TOKEN')) {
            $access_token = Cache::get('APPROVALSTUDIO_TOKEN');
            return $access_token;
        } else {
            $response = Http::post($this->apiURL.$this->tokenLoginPath, [
                'userName' => $this->username,
                'password' => $this->password
            ]);
            if ($response->ok()){
                $data = $response->json();
                $access_token = $data['result']['token'];
                Cache::put('APPROVALSTUDIO_TOKEN', $access_token, $this->tokenLifeSeconds);
                return $access_token;
            } else {
                throw new ApprovalstudioTokenInvalidException();
            }
        }
    }

    public function request(string $path, array $data = array(), $method='get', $filePath=''){
        switch($method){
            case 'get':
                $response = Http::withToken(self::getToken())->get($this->apiURL.$path, $data);
            break;
            case 'post':
                switch($path){
                    case'asset/upload':
                        $response = Http::withToken(self::getToken())->attach('attachment', file_get_contents($filePath), $data['FileName'])->post($this->apiURL.$path.'?'.http_build_query($data));
                    break;
                    default:
                        $response = Http::withToken(self::getToken())->post($this->apiURL.$path, $data); 
                    break;
                }
            break;
            case 'put':
                $response = Http::withToken(self::getToken())->put($this->apiURL.$path, $data);
            break;
            case 'delete':
                $response = Http::withToken(self::getToken())->delete($this->apiURL.$path, $data);
            break;
        }
        if ($response->ok()) {
            return $response->json();
        } else {
            dd($response);
            $message = "Approval.studio service requests failed. Please make sure Approval.studio service is reachable.";
            switch($path){
                case 'token​/login':
                    $message = "User with given email and password not found. Either the provided credentials are wrong or a user is locked and is no able to login anymore.";
                break;
                case 'project':
                    switch($method){
                        case 'get':
                            $message = "Project with the given ID not found or it has been already deleted.";
                        break;
                        case 'post':
                            $message = "Bad Request. One of the pre-requisites failed to validate";
                        break;
                        case 'put':
                            $message = "Project with the given ID not found or it has been already deleted.";
                        break;
                        case 'delete':
                            $message = "Project with the given ID not found and therefore project deletion failed.";
                        break;
                    }
                break;
                case 'project​/proofreport':
                    $message = "Project with the given ID not found and therefore proof report generation failed.";
                break;
                case 'project​/state':
                    $message = "Project with the given ID not found and therefore proof report generation failed.";
                break;
                case 'asset':
                    switch($method){
                        case 'get':
                            $message = "Asset with the given ID not found.";
                        break;
                        case 'post':
                            $message = "Asset with the given ID not found.";
                        break;
                        case 'put':
                            $message = "Project with the given ID not found or it has been already deleted.";
                        break;
                        case 'delete':
                            $message = "Project with the given ID not found and therefore project deletion failed.";
                        break;
                    }
                break;
                case 'asset/upload':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Project UID is either invalid or points to an non-existing or inactive project. Task UID is either invalid or points to an non-existing or inactive task.";
                        break;
                        case 412:
                            $message = "Only task types UploadAssets and UploadChangedAsset are allowed. In case when it is UploadChangedAsset task: file to upload must the the same type as the original file.";
                        break;
                    }
                break;
                case 'asset/download':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Asset with the given ID not found.";
                        break;
                    }
                break;
                case 'asset​/proofreport':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Asset with the given ID not found.";
                        break;
                    }
                break;
                case 'task/all':
                    $message = "Parameters validation failed.";
                break;
                case 'task':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Task with the given ID not found.";
                        break;
                    }
                break;
                case 'task/asset_upload':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Given project not found.";
                        break;
                        case 412:
                            $message = "You can not create a task for a project in state [Complete]. Only projects that are Active or OnHold can have tasks. OR User with UID [ZZZZZZZZZZZZZ] not found.";
                        break;
                    }
                break;
                case 'task​/refdoc_upload':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Given project not found.";
                        break;
                        case 412:
                            $message = "You can not create a task for a project in state [Complete]. Only projects that are Active or OnHold can have tasks. OR User with UID [ZZZZZZZZZZZZZ] not found.";
                        break;
                    }
                break;
                case 'task/review_asset':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Given project not found. OR Asset [AAAAAAAAAAAAAAAA1] not found… OR Asset [AAAAAAAAAAAAAAAA1] does not belong to project [XXXXXXXXXXXXXXXX].";
                        break;
                        case 412:
                            $message = "You can not create a task for a project in state [Complete]. Only projects that are Active or OnHold can have tasks. OR User with UID [ZZZZZZZZZZZZZ] not found.";
                        break;
                    }
                break;
                case 'task/review_asset_ext':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Given project not found. OR Asset [AAAAAAAAAAAAAAAA1] not found… OR Asset [AAAAAAAAAAAAAAAA1] does not belong to project [XXXXXXXXXXXXXXXX].";
                        break;
                        case 412:
                            $message = "You can not create a task for a project in state [Complete]. Only projects that are Active or OnHold can have tasks. OR User with UID [ZZZZZZZZZZZZZ] not found.";
                        break;
                    }
                break;
                case 'task/complete':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Given task not found.";
                        break;
                        case 410:
                            $message = "The task is not pending and can not be completed.";
                        break;
                        case 412:
                            $message = "This type of task can not be manually completed.";
                        break;
                    }
                break;
                case 'annotation/all':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Asset with the given ID not found.";
                        break;
                    }
                break;
                case 'annotation':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Annotation with the given ID not found.";
                        break;
                        case 412:
                            $message = "You can delete only your annotation.";
                        break;
                    }
                break;
                case 'annotation​/hide':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 406:
                            $message = "A user can hide only his/hers own annotation.";
                        break;
                        case 404:
                            $message = "Annotation with the given ID not found.";
                        break;
                    }
                break;
                case 'annotation​/complete':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Annotation with the given ID not found.";
                        break;
                        case 406:
                            $message = "Annotation is already completed.";
                        break;
                    }
                break;
                case 'annotation/uncomplete':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                        case 404:
                            $message = "Annotation with the given ID not found.";
                        break;
                        case 406:
                            $message = "Annotation is already completed.";
                        break;
                    }
                break;
                case 'users':
                    switch($response->status()){
                        case 400:
                            $message = "Parameters validation failed.";
                        break;
                    }
                break;
            }
            throw new ApprovalstudioRequestFailedException($message);
        }
    }
}