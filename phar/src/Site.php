<?php
use Phocker\Application;

class Site extends Application
{
    public function route_get_api_notes()
    {
        return $this->jsonResponse(
            $this->getNotes()
        );
    }

    public function route_post_api_notes()
    {
        $input = file_get_contents('php://input');
        $content = json_decode($input, true);
        $newNote = $this->createNote($content['content']);

        $response = [
            'response' => 'success',
            'note' => $newNote
        ];
        return $this->jsonResponse($response);
    }

    protected function initializeRoutes()
    {
        parent::initializeRoutes();

        $this->router->get(
            '/?',
            function() {
                return $this->displayPage('index');
            }
        );
    }

    public function handleRequest(?string $uri = null, string $method = null)
    {
        $handled = parent::handleRequest($uri, $method);
        if ($handled) {
            return $handled;
        }

        header('HTTP/1.1 404 Not Found');
        return $this->displayPage('404');
    }

    public function initialize()
    {
        $this->createDatabase();
    }

    public function createNote($content)
    {
        $this->database = $this->getDatabase();
        $stmt = $this->database->prepare('INSERT INTO notes (content) VALUES (:content)');
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->execute();

        $newNote = $this->database->query('SELECT * FROM notes ORDER BY id DESC LIMIT 1')->fetchArray();

        return $newNote;
    }

    public function getNotes(int $start = 0, int $limit = 10)
    {
        $this->database = $this->getDatabase();
        $results = $this->database->query("
            SELECT * FROM notes
            ORDER BY id DESC
            LIMIT {$start}, {$limit}
        ");
        $logs = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $logs[] = $row;
        }

        return $logs;
    }

    protected function getDatabase()
    {
        if($this->isPhar()) {
            $this->databaseFile = $this->currentDir . '/' . $this->databaseName;
            if(is_file($this->databaseFile)) {
                unlink($this->databaseFile);
            }

            $phar = new Phar($this->rootDir);
            $phar->extractTo($this->currentDir, $this->databaseName);
            $this->database = new SQLite3($this->databaseFile);

            return $this->database;
        }

        $this->database = new SQLite3($this->databaseFile);
        return $this->database;
    }

    protected function createDatabase()
    {
        if(!is_dir(dirname($this->databaseFile))) {
            mkdir(dirname($this->databaseFile), 0754, true);
        }

        $this->database = $this->getDatabase();
        $this->database->exec('CREATE TABLE notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        $this->database->exec("
            INSERT INTO notes (content)
            VALUES (
                'Welcome to Phocker !'
            )
        ");

    }
}
