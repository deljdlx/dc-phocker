<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐘</text></svg>">
    <title>Phocker - a funny PHP website solution

    </title>
    <link rel="stylesheet" href="<?=asset('css/styles.css')?>" />
</head>

<body>
    <header
    style="
                background-image:
                    linear-gradient(
                    #f57c00dd,
                    #f57c00dd
                    ),
                    url('<?=asset('images/phocker.png')?>')
                ;
                background-size: 50%;
                background-repeat: no-repeat;
                background-position: center;
            "
    >
        <h1>Welcome to Phocker</h1>
        <p
            style="
                max-width: 600px;
                margin: 0 auto;
                text-align: center;
        ">
            Phocker is an experimental, all-in-one PHP website solution that you can run just by using a single .phar file. Perfect for quickly prototyping APIs or tinkering with small services, Phocker is designed for rapid experimentation and fun — but definitely not for production use — unless you like living on the edge!
        </p>
    </header>

    <div class="content" id="about">
        <h2>Starting with Phocker</h2>
        <div>
            <h3>Requirements</h3>
            <div class="about__content">
                <p>Phocker requires PHP 8 or higher to run.</p>
                <p>Option <strong>phar.readonly</strong> must be set to 0 in php.ini</p>
                <p>
                    Optionnal : a "data" folder must be created in the same directory as the phar file and must be writable
                </p>
                <p>
                    If the data folder is not present, the application will try to create it.
                </p>
            </div>

            <h3>Installation</h3>
            <div class="about__content">

                <ol>
                    <li>Download</li>
                    <li>Start server : <code>php -S 0.0.0.0:8080 phocker.phar</code></li>
                    <li>Open your browser and go to <code>http://localhost:80</code></li>
                    <li>Thats all !</li>
                </ol>
            </div>
            <a href="/download" class="btn">Download</a>

        <h2>Modifying source code</h2>
        <div style="
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        ">
            <div>
                <h3>Extracting sources</h3>
                <p>
                    You can extract sources code of the phar file by running the following command in the same directory as the phar file:
                </p>
                <code>
                    php phocker.phar -u
                </code>
                <p>
                    Files will be extracted into a phar directory..
                <p>
            </div>

            <div>
                <h3>Editing files</h3>
                <p>
                    You can edit the source code in the phar directory. To test the changes, you can run the following command:
                </p>
                <code>
                    php -S 0.0.0.0:8080 phar/bootstrap.php
                </code>
            </div>

            <div>
                <h3>Recompiling the phar file</h3>
                <p>
                    After modifying the source code, you can recompile the phar file with the following command in phar directory:
                </p>
                <code>
                    php bootstrap.php -c
                </code>
            </div>

        </div>
    </div>

    <section>
        <h2>API</h2>
        <div style="
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        ">
        <ul>
            <li><a href="/api/phocker/version">/api/phocker/version</a> : Get the version of the phar file</li>
            <li><a href="/api/phocker/files">/api/phocker/files</a> : Get the list of files in the phar file</li>
        </ul>
        </div>


    </section>


    <section class="demo">

        <div class="form-container">
            <h2>Create a New Note</h2>
            <form action="/api/notes" method="POST">
                <textarea name="content" rows="4" placeholder="Enter your note content here..." required></textarea>
                <button type="submit">Create Note</button>
            </form>
        </div>

        <div class="notes-container"></div>
    </section>


    <script src="<?=asset('js/application.js')?>"></script>

</body>

</html>
