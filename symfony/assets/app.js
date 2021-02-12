/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
const $ = require('jquery');
require('bootstrap');
window.bootbox = require('bootbox');
bootbox.setLocale('fr');

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

console.log('Application started');

$(document).ready(function(e){

    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    
    // flag to avoid multiple click during ajax loading
    var $pending = false;
    // character delete
    $('.btn-character-delete').on('click', function(e) {
          if (!$pending) {
              $pending = true;
              var $this = $(this);
              var $id = $this.attr('character_id');

              bootbox.confirm({
                  size: "small",
                  message: "Es-tu vraiment sûr de supprimer ce personnage ? Cette action sera définitive !",
                  callback: function(result){
                      if (result) {
                          $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
                          $.ajax({
                              url: '/character/deleteajax/id/' + $id,
                              method: "GET",
                              data: {}
                          }).then(function(response) {
                              if (response.error === false) {
                                  $("#character-wrapper-" + $id).remove();
                              } else {
                                  $this.html('<i class="fas fa-trash"></i>');
                                  var $message = 'message' in response ? response.message : "Impossible de supprimer cet enregistrement";
                                  $('<div class="alert alert-warning alert-dismissible fade show small" role="alert">' + $message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>').insertAfter("#character-wrapper-" + $id + " .card-body");
                              }
                              $pending = false;
                          });
                      } else {
                          $pending = false;
                      }
                  }
              });
          }
    });
    // event delete
    $('.btn-event-delete').on('click', function(e) {
          if (!$pending) {
              $pending = true;
              var $this = $(this);
              var $id = $this.attr('event_id');

              bootbox.confirm({
                  size: "small",
                  message: "Es-tu vraiment sûr de supprimer cet évènement ? Cette action sera définitive !",
                  callback: function(result){
                      if (result) {
                          $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
                          $.ajax({
                              url: '/event/deleteajax/id/' + $id,
                              method: "GET",
                              data: {}
                          }).then(function(response) {
                            console.log(response.error);
                              if (response.error === false) {
                                console.log("#event-wrapper-" + $id);
                                  $("#event-wrapper-" + $id).remove();
                              } else {
                                  $this.html('<i class="fas fa-trash"></i>');
                                  var $message = 'message' in response ? response.message : "Impossible de supprimer cet enregistrement";
                                  $('<div class="alert alert-warning alert-dismissible fade show small" role="alert">' + $message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>').insertAfter("#event-wrapper-" + $id + " .card-body");
                              }
                              $pending = false;
                          });
                      } else {
                          $pending = false;
                      }
                  }
              });
          }
    });
    // category delete
    $('.btn-category-delete').on('click', function(e) {
          if (!$pending) {
              $pending = true;
              var $this = $(this);
              var $id = $this.attr('category_id');

              bootbox.confirm({
                  size: "small",
                  message: "Es-tu vraiment sûr de supprimer cette catégorie ? Cette action sera définitive !",
                  callback: function(result){
                      if (result) {
                          $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
                          $.ajax({
                              url: '/category/deleteajax/id/' + $id,
                              method: "GET",
                              data: {}
                          }).then(function(response) {
                              if (response.error === false) {
                                  $("#category-wrapper-" + $id).remove();
                              } else {
                                  $this.html('<i class="fas fa-trash"></i>');
                                  var $message = 'message' in response ? response.message : "Impossible de supprimer cet enregistrement";
                                  $("#category-wrapper-" + $id).append('<div class="alert alert-warning alert-dismissible fade show small" role="alert">' + $message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                              }
                              $pending = false;
                          });
                      } else {
                          $pending = false;
                      }
                  }
              });
          }
    });
    // category delete
    $('.btn-timeline-delete').on('click', function(e) {
        if (!$pending) {
            $pending = true;
            var $this = $(this);
            var $id = $this.attr('timeline_id');

            bootbox.confirm({
                size: "small",
                message: "Es-tu vraiment sûr de supprimer cette frise ? Cette action sera définitive !",
                callback: function(result){
                    if (result) {
                        $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
                        $.ajax({
                            url: '/timeline/deleteajax/id/' + $id,
                            method: "GET",
                            data: {}
                        }).then(function(response) {
                            if (response.error === false) {
                                $("#timeline-wrapper-" + $id).remove();
                            } else {
                                $this.html('<i class="fas fa-trash"></i>');
                                var $message = 'message' in response ? response.message : "Impossible de supprimer cet enregistrement";
                                $("#timeline-wrapper-" + $id).append('<div class="alert alert-warning alert-dismissible fade show small" role="alert">' + $message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                            }
                            $pending = false;
                        });
                    } else {
                        $pending = false;
                    }
                }
            });
        }
    });
    // Global confirmation box to all delete a link
    $('.btn-delete').on('click', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        return bootbox.confirm('Es-tu vraiment sûr de vouloir supprimer ce contenu ?', function(result) {
            if (result) {
                window.location = href
            }
        });
    });
});
