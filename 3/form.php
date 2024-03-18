<!DOCTYPE html>

<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="images/icon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="style.css" />
    <title>Задание 3</title>
</head>

<body>

<header class="header">
    <img class="logo" src="images/logo.png" alt="Логотип" />
    <h1 class="title">Задание 3</h1>
</header>

<div class="content">
    <div class="application-form">
        <form action="./index.php" method="post">
            <div class="form-block-element">
                <label class="form-text label" for="fullName">
                    ФИО
                </label>

                <div class="form-element">
                    <input id="fullName"
                           name="fullName"
                           type="text"
                           placeholder="Введите ФИО"
                           size="30"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="phoneNumber">
                    Телефон
                </label>

                <div class="form-element">
                    <input id="phoneNumber"
                           name="phoneNumber"
                           type="tel"
                           placeholder="Введите номер телефона"
                           size="30"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="email">
                    E-mail
                </label>

                <div class="form-element">
                    <input id="email"
                           name="email"
                           type="email"
                           placeholder="Введите e-mail"
                           size="30"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="birth">
                    Дата рождения
                </label>

                <div class="form-element">
                    <input id="birth"
                           name="birth"
                           type="date"
                           size="30"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <div class="form-text label">Укажите ваш пол</div>

                <div class="form-element">
                    <div>
                        <input id="male" type="radio" name="sex" value="male"/>
                        <label class="form-text" for="male">М</label>
                        <input id="female" type="radio" name="sex" value="female"/>
                        <label class="form-text" for="female">Ж</label>
                    </div>
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="programLanguages">
                    Укажите любимые <br /> языки <br /> программирования
                </label>

                <div class="form-element">
                    <select id="programLanguages"
                            name="programLanguages"
                            size="5"
                            multiple="multiple"
                    >
                        <option value="Pascal">Pascal</option>
                        <option value="C">C</option>
                        <option value="C++">C++</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="PHP">PHP</option>
                        <option value="Python">Python</option>
                        <option value="Java">Java</option>
                        <option value="Haskel">Haskel</option>
                        <option value="Clojure">Clojure</option>
                        <option value="Prolog">Prolog</option>
                        <option value="Scala">Scala</option>
                    </select>
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="bio">
                    Биография
                </label>

                <div class="form-element">
                    <textarea class="textarea" id="bio" name="bio" placeholder=""></textarea>
                </div>
            </div>

            <div class="form-block-element">
                <div class="checkFormElement">
                    <label>
                        <input type="checkbox" name="check" />
                        <b class="form-text">С контрактом ознакомлен(а)</b>
                    </label>
                </div>
            </div>

            <div class="form-block-element">
                <p><input class="sendButton" type="submit" name="save" value="Сохранить"></p>
                <p class="form-text"><i class="service-message"></i></p>
            </div>
        </form>
    </div>
</div>

<div class="footer">
    <footer>
        <div class="footer-content">
            (c) Петров Семён, 2024
        </div>
    </footer>
</div>

</body>

</html>