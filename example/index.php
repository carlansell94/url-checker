<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <script>
            addEventListener('load', (event) => {
                const form = document.querySelector('[name="example-form"]');

                form.addEventListener('submit', (e) => {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const container = document.querySelector('#inner-container');
                    container.innerHTML = "</div>Running...</div>";
                    document.querySelector('#input-controls').style.display = "none";

                    postData(formData)
                        .then((data) => {
                            container.innerHTML = '<div id="response">'
                                + '<pre>' + JSON.stringify(data, null, 2) + '</pre>'
                                + '</div>'
                                + '<a href="./output.csv" download="./output.csv">Download CSV</a>'
                                + '<a onClick="window.location.reload();">Retry</a>'
                        }
                    );
                });
            });

            async function postData(formData) 
            {
                const response = await fetch('./run.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.status == 200) {
                    return response.json();
                } else {
                    return "An error has occurred"
                }
            }
        </script>
    </head>
    <body>
        <div id="container">
            <form name="example-form">
                <h1>URL checker</h1>
                <div id="inner-container"></div>
                <div id="input-controls">
                    <h2>Input CSV File:</h2>
                    <input type="file" name="file" id="file" />
                    <input type="submit" name="submit" />
                </div>
            </form>
        </div>
    </body>
</html>