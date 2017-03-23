

var app = angular.module('myApp',[]);
app.controller('myCtrl', function ($scope) {
    // todo - tablica obiektow zadan do wykonania
    $scope.todo = [
        {job: "umyć naczynia", status: false},
        {job: "pójść spać", status: false},
    ];
    // getIloscZadan - podaje ilosc zdan wpisanych do listy
    $scope.getIloscZadan = function () {
        return $scope.todo.length;
    };
    //getIloscDoZrobienia  - podaje ilosc zadan na liscie ze statusem nieukonczone
    $scope.getIloscDoZrobienia = function () {
        var wynik = 0;
        for (i=0; i<$scope.todo.length; i++){
            if($scope.todo[i].status == false){
                wynik++
            };
        };
        return wynik;
    };
    // dodajZadanie - dodaje kolejne zadanie na koniec listy
    $scope.dodajZadanie = function (){
        $scope.todo.push(
            {job: $scope.zadanie, status: false}
        );
        $scope.zadanie = '';
    };
    // skasujWykonane - skasuje wszystkie zdania wykonane ( status == true )
    $scope.skasujWykonane = function () {
        for (i=$scope.todo.length-1; i>=0; i-- ){
            if ($scope.todo[i].status == true){
                $scope.todo.splice(i,1);
            };
        };
    }


        
});