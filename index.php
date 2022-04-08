<?php

/*
    Необходимо доработать класс рассылки Newsletter, что бы он отправлял письма
    и пуш нотификации для юзеров из UserRepository.

    За отправку имейла мы считаем вывод в консоль строки: "Email {email} has been sent to user {name}"
    За отправку пуш нотификации: "Push notification has been sent to user {name} with device_id {device_id}"

    Так же необходимо реализовать функциональность для валидации имейлов/пушей:
    1) Нельзя отправлять письма юзерам с невалидными имейлами
    2) Нельзя отправлять пуши юзерам с невалидными device_id. Правила валидации можете придумать сами.
    3) Ничего не отправляем юзерам у котоых нет имен
    4) На одно и то же мыло/device_id - можно отправить письмо/пуш только один раз

    Для обеспечения возможности масштабирования системы (добавление новых типов отправок и новых валидаторов),
    можно добавлять и использовать новые классы и другие языковые конструкции php в любом количестве.
    Реализация должна соответствовать принципам ООП
*/

class UserRepository
{
    public function getUsers(): array
    {
        return [
            [
                'name' => 'Ivan',
                'email' => 'ivan@test.com',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Peter',
                'email' => 'peter@test.com'
            ],
            [
                'name' => 'Mark',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Nina',
                'email' => '...'
            ],
            [
                'name' => 'Luke',
                'device_id' => 'vfehlfg43g'
            ],
            [
                'name' => 'Zerg',
                'device_id' => ''
            ],
            [
                'email' => '...',
                'device_id' => ''
            ]
        ];
    }
}

$userRepository = new UserRepository();
$userRepository->getUsers();

class Newsletter
{
    public $users = [];
    public $userRepository;
    public $newsLetterAlreadySend = [];
    public $body;
    public $subject;

    public function __construct(object $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserData()
    {
        return $this->users = $this->userRepository->getUsers();
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function emailIsValid($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function deviceIdIsValid($deviceId)
    {
        return ($deviceId != NULL && strlen($deviceId) > 8 && strlen($deviceId) < 15);
    }

    /**
     * method gets list of emails\device_id wich was already send from DB or other source
     */
    public function getNewsLetterAlreadySend()
    {
        $this->newsLetterAlreadySend = []; //array gets data from database
        $this->addNewsLetterAlreadySend('Ks[dqweer4'); //for the test, a value was added to the array
    }

    public function addNewsLetterAlreadySend($id)
    {
        $this->newsLetterAlreadySend[] = $id;
    }

    /**
     * the method checks if the mailing list was sent to this email address earlier
     */
    public function checkNewsletterSend($id)
    {
        return !in_array($id, $this->newsLetterAlreadySend);
    }

    public function sendMail($user)
    {
        if (!isset($user['email']) || !$this->emailIsValid($user['email'])) {
            echo "Email is not valid!" . PHP_EOL;
            return;
        }

        if (!$this->checkNewsletterSend($user['email'])) {
            echo "Email is already send early!" . PHP_EOL;
            return;
        }

        if (empty($user['name'])) {
            echo "Name is not specified!" . PHP_EOL;
            return;
        }

        $result = mail($user['email'], $this->subject, $this->body);
        if ($result) {
            echo "Email {$user['email']} has been sent to user:  {$user['name']}" . PHP_EOL;
            $this->addNewsLetterAlreadySend($user['email']);
        }
    }

    public function sendPush($user)
    {
        if (!isset($user['device_id']) || !$this->deviceIdIsValid($user['device_id'])) {
            echo "Device_id is not valid!" . PHP_EOL;
            return;
        }

        if (empty($user['name'])) {
            echo "Name is not specified!" . PHP_EOL;
            return;
        }

        if (!$this->checkNewsletterSend($user['device_id'])) {
            echo "Push notification for this user {$user['device_id']} hase ben send already!" . PHP_EOL;
            return;
        }

        //TODO: command to send a push message
        $result = true;
        if ($result) {
            echo "Push notification has been sent to user: {$user['name']} with device_id: {$user['device_id']}" . PHP_EOL;
            $this->addNewsLetterAlreadySend($user['device_id']);
        }

    }

    public static function send($userRepository, $body, $subj): void
    {
        $newsletter = new Newsletter($userRepository);
        $newsletter->setSubject($subj);
        $newsletter->setBody($body);
        $newsletter->getUserData();
        $newsletter->getNewsLetterAlreadySend();

        foreach ($newsletter->users as $user) {
            $newsletter->sendMail($user);
            $newsletter->sendPush($user);
        }


    }
}

Newsletter::send($userRepository, 'Hello wold!', 'test letter');


/**
 * Тут релизовать получение объекта(ов) рассылки Newsletter и вызов(ы) метода send()
 * $newsletter = //... TODO
 * $newsletter->send();
 * ...
 */