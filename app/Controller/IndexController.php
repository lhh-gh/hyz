<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\View\RenderInterface;

class IndexController extends AbstractController
{
    public function index(RenderInterface $render)
    {
        if (! $this->isLogin($this->request)) {
            return $this->response->redirect('/login');
        }

        $info = $this->request->getCookieParams();
        $user = json_decode($info['USER_INFO'] ?? '{}', true);
        $host = $this->request->getHeaderLine('host');
        $data = array_merge(is_array($user) ? $user : [], ['host' => $host]);

        return $render->render('index', $data);
    }

    public function login(RequestInterface $request, ResponseInterface $response, RenderInterface $render)
    {
        $action = $request->post('action');
        $account = trim((string) $request->post('account', ''));
        $tips = '';

        if ($action === 'login') {
            if ($account !== '') {
                $cookie = new Cookie('USER_INFO', json_encode(['account' => $account], JSON_UNESCAPED_UNICODE));

                return $response->withCookie($cookie)->redirect('/');
            }

            $tips = '用户账号不能为空';
        }

        return $render->render('login', ['tips' => $tips]);
    }

    private function isLogin(RequestInterface $request): bool
    {
        $cookieInfo = $request->getCookieParams();
        if (! isset($cookieInfo['USER_INFO'])) {
            return false;
        }

        $user = json_decode($cookieInfo['USER_INFO'], true);

        return is_array($user) && ! empty($user['account']);
    }
}
