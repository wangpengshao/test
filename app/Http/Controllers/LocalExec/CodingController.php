<?php

namespace App\Http\Controllers\LocalExec;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CodingController extends Controller
{
    private $token = 'bnhLLOcgfYhyc2f';

    public function saveGitPull(Request $request)
    {
        $signature = $request->header('X-Coding-Signature');
        $payload = file_get_contents('php://input');
        $path = base_path();
        $php = '/usr/local/php/bin/php';
        if ($this->isFromGithub($payload, $signature)) {
//            $command = "cd {$path} && git pull && {$php} artisan opcache:clear  && {$php} artisan config:cache && " .
//                " {$php} artisan route:cache && {$php} artisan view:cache ";
            $command = "cd {$path} && git pull && {$php} artisan opcache:clear  && {$php} artisan config:clear && " .
                " {$php} artisan config:cache && {$php} artisan route:clear && {$php} artisan route:cache && {$php} artisan view:cache ";
            $response = shell_exec($command);
            http_response_code(200);
            echo $response;
        } else {
            abort(403);
        }
        exit();
    }

    public function saveGitPullVue(Request $request)
    {
        $signature = $request->header('X-Coding-Signature');
        $payload = file_get_contents('php://input');
        $path = '/tcsoft/webGit/newUweiVue';

        if ($this->isFromGithub($payload, $signature)) {
            set_time_limit(0);
            $a = shell_exec("cd {$path} && /usr/bin/git pull && ./deploy.sh");
            echo '<pre />';
            echo $a;
            echo '<pre />';
            http_response_code(200);
        } else {
            abort(403);
        }
        exit();
    }

    private function isFromGithub($payload, $signature)
    {
        return 'sha1=' . hash_hmac('sha1', $payload, $this->token, false) === $signature;
    }

}
