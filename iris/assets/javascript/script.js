/**
 * Created by Tara on 4/5/2015.
 */
angular.module('app', []);

angular.module('app').controller('mailCtrl',function($scope){
$scope.openModal=function(){
    $scope.modalOpen =true;
}
});

angular.module('app').directive('modal', function($document){
    return{
      scope:{
          modalOpen: '=open',
        options: '=',
        onClose:'&'
      },
        transclude: true,
        templateUrl:'modal.html',
        controller: function($scope){

        },
        link: function($scope, el, attrs){
            var options = angular.extend( {
                height:'250px',
                width: '500px',
                top:'20%',
                left: '30%'
            },$scope.options);

            el.find('.modal-container').css({
                'left' :options.left,
                'top' :options.top,
                'height' :options.height + 'px',
                'width' :options.width + 'px'
            })
            var pageHeight= $document.height();
            var pageWidth= $document.width();
            el.find('.modal-blackout').css({
                'width':pageWidth + 'px',
                'height': pageHeight + 'px'
            })

    }
    }
})
