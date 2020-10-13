(function() {
    'use strict';
    angular
        .module('app')
        .controller('HomeController', HomeController);
    HomeController.$inject = ['$rootScope', '$http', '$interval'];

    function HomeController($rootScope, $http, $interval) {
        var vm = this;
        var promise;
        vm.match = false;
        vm.match_id = '';
        vm.commentary = [];
        vm.attack = attack;
        vm.startgame = startGame;
        vm.countdown = 60;
        vm.logout = logout;

        if (localStorage.getItem('match_id') != null) {
            vm.match_id = localStorage.getItem('match_id');
        }

        var counter = function() {
            vm.countdown -= 1;
            if (vm.countdown < 1 && vm.countdown > -1) {
                stopCountDown();
                timeUpGame();
                return;
            }
        };
        var startCountDown = function() {
            stopCountDown();
            promise = $interval(counter, 1000, vm.countdown)
        };
        var stopCountDown = function() {
            $interval.cancel(promise);
            promise = '';
        };

        function loadMatchDetails() {
            if (localStorage.getItem('match_id') != null) {
                vm.match_id = localStorage.getItem('match_id');
            } else {
                vm.match_id = '';
            }
            vm.commentary = [];
            vm.user_health = 100;
            vm.monster_health = 100;
            if (vm.match_id != '') {
                var parameter = JSON.stringify({ match_id: vm.match_id });
                $http({
                    method: "POST",
                    data: parameter,
                    url: "http://localhost/game/api/v1/get_match_details.php",
                    headers: { 'Authorization': $rootScope.token, 'Content-Type': 'application/json' }
                }).then(function(response) {
                    var data = response.data;
                    if (data.status == 200) {
                        if (data.hasOwnProperty('data')) {
                            vm.user_health = data.data.user_health;
                            vm.monster_health = data.data.monster_health;
                            vm.finish = data.data.finish;
                            vm.countdown = data.data.countdown;
                            if (vm.finish) {
                                vm.match = false;
                                vm.match_id = '';
                                if (localStorage.getItem('match_id') != null) {
                                    localStorage.removeItem('match_id');
                                }
                            } else {
                                startCountDown();
                            }
                        }
                        if (data.hasOwnProperty('commentatory')) {
                            vm.commentary = data.commentatory;
                        }
                    } else if (data.status == 208) {
                        vm.match = false;
                        vm.match_id = '';
                    }
                }, function(error) {
                    vm.dataLoading = false;
                    console.log(error);
                });
            }
        }

        function timeUpGame() {
            attack('timeup');
        }

        function startGame() {
            if (vm.match_id != '') {
                vm.match = true;
                loadMatchDetails();
                if (vm.match) {
                    return;
                }
            }
            $http({
                method: "POST",
                url: "http://localhost/game/api/v1/start_match.php",
                headers: { 'Authorization': $rootScope.token }
            }).then(function(response) {
                vm.dataLoading = false;
                var data = response.data;
                if (data.status == 200) {
                    alert(data.message);
                    if (data.hasOwnProperty('data')) {
                        vm.user_health = data.data.user_health;
                        vm.monster_health = data.data.monster_health;
                        vm.finish = data.data.finish;
                        vm.match_id = data.data.match_id;
                        localStorage.setItem("match_id", data.data.match_id);
                        vm.match = true;
                        vm.countdown = data.data.countdown;
                        startCountDown();
                    }
                } else {
                    alert(data.message);
                }

                if (data.hasOwnProperty('commentatory')) {
                    vm.commentary = data.commentatory;
                }
            }, function(error) {
                vm.dataLoading = false;
                console.log(error);
            });
        }

        function attack(type) {
            if (vm.match_id == '') {
                alert('Please start a new game');
                vm.match = false;
                return;
            }
            var parameter = JSON.stringify({ request_type: type, match_id: vm.match_id });
            vm.dataLoading = true;
            $http({
                method: "POST",
                data: parameter,
                url: "http://localhost/game/api/v1/attack.php",
                headers: { 'Authorization': $rootScope.token, 'Content-Type': 'application/json' }
            }).then(function(response) {
                vm.dataLoading = false;
                console.log(response);
                var data = response.data;
                if (data.status == 200 || data.status == 208) {
                    if (data.hasOwnProperty('data')) {
                        vm.user_health = data.data.user_health;
                        vm.monster_health = data.data.monster_health;
                        vm.finish = data.data.finish;
                        vm.countdown = data.data.countdown;
                        if (vm.finish) {
                            vm.match = false;
                            vm.match_id = '';
                            if (localStorage.getItem('match_id') != null) {
                                localStorage.removeItem('match_id');
                            }
                            stopCountDown();
                            alert(data.message);
                        } else {
                            startCountDown();
                        }
                    } else {
                        alert(data.message);
                    }
                    if (data.hasOwnProperty('commentatory')) {
                        vm.commentary = data.commentatory;
                    }
                } else {
                    alert(data.message);
                    if (localStorage.getItem('match_id') != null) {
                        vm.match_id = localStorage.removeItem('match_id');
                    }
                    vm.match = false;
                }
            }, function(error) {
                vm.dataLoading = false;
                console.log(error);
            });
        }

        function logout() {
            vm.dataLoading = true;
            $http({
                method: "GET",
                url: "http://localhost/game/api/v1/logout.php",
                headers: { 'Authorization': $rootScope.token }
            }).then(function(response) {
                var data = response.data;
                if (data.status == 200) {
                    vm.match = false;
                    vm.match_id = '';
                    if (localStorage.getItem('match_id') != null) {
                        localStorage.removeItem('match_id');
                    }
                    if (localStorage.getItem('user_data') != null) {
                        localStorage.removeItem('user_data');
                    }
                    stopCountDown();
                } else {
                    alert(data.message);
                    if (localStorage.getItem('match_id') != null) {
                        vm.match_id = localStorage.removeItem('match_id');
                    }
                    vm.match = false;
                }
            }, function(error) {
                vm.dataLoading = false;
                console.log(error);
            });
        }
        loadMatchDetails();
    }
})();