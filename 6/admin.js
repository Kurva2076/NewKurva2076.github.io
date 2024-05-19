document.addEventListener("DOMContentLoaded", () => {
    ShowStatisticTable();
    FindUser();
    PagingUsers();
});

function ShowStatisticTable() {
    const showButton = document.querySelector('.show-table-button > button');
    showButton.onclick = function () {
        const tableWrapper = document.querySelector('.program-langs-statistic-table > .wrapper');
        const opacity = window.getComputedStyle(tableWrapper).getPropertyValue('opacity');
        tableWrapper.style.opacity = (opacity === "0") ? "1" : "0";
    };
}

function FindUser() {
    const findButton = document.querySelector('.find-button');
    findButton.onclick = () => {
        const userId = document.getElementById('FindUserId').value;
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    document.querySelector('.users-info-block').innerHTML = xhr.responseText;
                    document.querySelector('.appearance-management > .pagination').innerHTML = '';
                    const pagingButtons = document.querySelectorAll('.cnt-users-buttons > button');
                    for (const pagingButton of pagingButtons) {
                        pagingButton.style = '';
                    }

                    if (userId === "") {
                        const pagingButton = document.querySelector('.cnt-users-buttons > .all-users');
                        pagingButton.style.opacity = "1";
                    }
                } else {
                    console.error('Ошибка поиска пользователя:', xhr.status);
                }
            }
        };
        xhr.open('GET', 'users_table.php?user_id=' + userId, true);
        xhr.send();
    };
}

function PagingUsers() {
    const cntUsers = Number(document.querySelector('.cnt-users-block > h3 > b').innerHTML);
    const pagingButtons = document.querySelectorAll('.cnt-users-buttons > button');
    for (const pagingButton of pagingButtons) {
        const usersCntOnPage = pagingButton.innerText;
        pagingButton.onclick = () => {
            for (const anyPagingButton of pagingButtons) {
                anyPagingButton.style.opacity = '';
            }
            pagingButton.style.opacity = '1';

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = () => {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        if (/^\d+$/.test(usersCntOnPage)) {
                            const cntPages = Math.ceil(
                                cntUsers / usersCntOnPage
                            );

                            let pagination = '';
                            if (cntPages > 1) {
                                for (let i = 1; i <= cntPages; i++) {
                                    if (i == 1) {
                                        pagination += "<button type='button' style='opacity: 0.25'><b>" +
                                            i.toString() + "</b></button>";
                                    } else if (i < 6 || i == cntPages || cntPages < 8) {
                                        pagination += "<button type='button'><b>" + i.toString() + "</b></button>";
                                    } else {
                                        pagination += "<button type='button'><b>...</b></button>";
                                        i = cntPages - 1;
                                    }
                                }
                            }

                            document.querySelector('.appearance-management > .pagination').innerHTML = pagination;
                            PagingChange(usersCntOnPage);
                        } else {
                            document.querySelector('.appearance-management > .pagination').innerHTML = '';
                        }

                        document.querySelector('.users-info-block').innerHTML = xhr.responseText;
                    } else {
                        console.error('Ошибка:', xhr.status);
                    }
                }
            };
            xhr.open('GET', 'users_table.php?cnt_user_on_page=' + usersCntOnPage + '&page=1', true);
            xhr.send();
        };
    }
}

function PagingChange(usersCntOnPage) {
    const pagingButtons = document.querySelectorAll('.pagination > button');
    let maxPageNum = 0;
    for (const pagingButton of pagingButtons) {
        let pageNum = pagingButton.innerText;

        if (pageNum !== "...") {
            pageNum = Number(pageNum);
            maxPageNum = Math.max(maxPageNum, pageNum);

            pagingButton.onclick = () => {
                let pagination = '';
                for (let i = 1; i <= maxPageNum; i++) {
                    let page;
                    if (pageNum >= 1 && pageNum <= 3) {
                        if (i < 6 || i == maxPageNum || maxPageNum < 8) {
                            page = i.toString();
                        } else {
                            page = "...";
                            i = maxPageNum - 1;
                        }
                    } else if (pageNum >= maxPageNum - 3 && pageNum <= maxPageNum) {
                        if (i > maxPageNum - 5 || i == 1 || maxPageNum < 8) {
                            page = i.toString();
                        } else {
                            page = "...";
                            i = maxPageNum - 5;
                        }
                    } else if (pageNum > 3 && pageNum < maxPageNum - 3) {
                        if (i == 1 || i == maxPageNum || maxPageNum < 8 || (i >= pageNum - 1 && i <= pageNum + 1)) {
                            page = i.toString();
                        } else {
                            page = "...";
                            i = (i == 2) ? pageNum - 2 : maxPageNum - 1;
                        }
                    }

                    pagination += "<button type='button'" +
                        ((page === pageNum.toString()) ? " style='opacity: 0.25'>" : ">") +
                        "<b>" + page + "</b></button>";
                }

                document.querySelector('.appearance-management > .pagination').innerHTML = pagination;

                const xhr = new XMLHttpRequest();
                xhr.onreadystatechange = () => {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            document.querySelector('.users-info-block').innerHTML = xhr.responseText;
                            PagingChange(usersCntOnPage);
                        } else {
                            console.error('Ошибка:', xhr.status);
                        }
                    }
                };
                xhr.open('GET', 'users_table.php?cnt_user_on_page=' + usersCntOnPage + '&page=' + pageNum, true);
                xhr.send();
            };
        }
    }
}