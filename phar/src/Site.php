<?php
use Phocker\Application;

class Site extends Application
{
    public function route_get_api_notes(): bool
    {
        return $this->jsonResponse(
            $this->getNotes()
        );
    }

    public function route_post_api_notes(): bool
    {
        $input = file_get_contents('php://input');
        if(!$input) {
            return $this->jsonResponse([
                'response' => 'error',
                'message' => 'No input provided'
            ]);
        }
        $content = json_decode($input, true);

        if(!is_array($content) || !is_string($content['content'])) {
            return $this->jsonResponse([
                'response' => 'error',
                'message' => 'Invalid input'
            ]);
        }
        $newNote = $this->createNote($content['content']);

        $response = [
            'response' => 'success',
            'note' => $newNote
        ];
        return $this->jsonResponse($response);
    }

    protected function initializeRoutes(): void
    {
        parent::initializeRoutes();

        $this->router->get(
            '/?',
            function() {
                return $this->displayPage('index');
            }
        );
    }

    public function handleRequest(string $uri, string $method): bool
    {
        $handled = parent::handleRequest($uri, $method);
        if ($handled) {
            return $handled;
        }

        header('HTTP/1.1 404 Not Found');
        return $this->displayPage('404');
    }

    public function initialize(): void
    {
        $this->createDatabase();
    }

    /**
     * @param string $content
     * @return array<string, string>
     */
    public function createNote(string $content): array
    {
        $this->database = $this->getDatabase();
        $stmt = $this->database->prepare('INSERT INTO notes (content) VALUES (:content)');

        if(!$stmt) {
            return [];
        }

        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->execute();

        $result = $this->database->query('SELECT * FROM notes ORDER BY id DESC LIMIT 1');
        if(!$result) {
            return [];
        }

        $newNote = $result->fetchArray();
        if(!$newNote) {
            return [];
        }

        return $newNote;
    }

    /**
     * @param integer $start
     * @param integer $limit
     * @return array<array<string, string>>
     */
    public function getNotes(int $start = 0, int $limit = 10): array
    {
        $this->database = $this->getDatabase();
        $results = $this->database->query("
            SELECT * FROM notes
            ORDER BY id DESC
            LIMIT {$start}, {$limit}
        ");

        if(!$results) {
            return [];
        }

        $logs = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $logs[] = $row;
        }

        return $logs;
    }

    protected function getDatabase(): SQLite3
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

    protected function createDatabase(): void
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
