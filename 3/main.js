document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#application-form > form');
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        const request = new XMLHttpRequest();
        request.open(`${this.method}`, `${this.action}`);
        request.send(formData);

        request.onload = function () {
            if (request.status === 200) {
                console.log(`Данные успешно отправлены на сервер.`);
                alert(request.response);
            }
            else
                console.log(`Возникла ошибка. ${request.status}: ${request.statusText}`);
        }

        request.onerror = function () {
            console.log(`${request.response}`)
        }

        // form.reset();
    });

    // const request = new XMLHttpRequest();
    // request.onreadystatechange = function() {
    //     if(request.readyState === 4) {
    //         if(request.status === 200) {
    //             console.log(request.responseText);
    //         } else {
    //             console.log('Error Code: ' +  request.status);
    //             console.log('Error Message: ' + request.statusText);
    //         }
    //     }
    // }
    // request.open('GET', './index.php');
    // request.send();
});