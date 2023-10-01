var baseUrl = $('.baseUrl').val();

// common functions
/************************************************/
function getAjaxJSON(urlChange)
{
  
  $.ajax({
      url: urlChange,
      type: 'GET',
      dataType: 'json',
      beforeSend: function()
      {
        $('#main').html("<p>Page loading ...<img src='/images/indicator.gif'><p>");
        //alert('wait here');
      },
      success: function(data)
      {
        //console.log('here');
        $('title').html(data.title);
        var showObject;
        if(data.feedback === 1 ) // show the page
        {
          showObject = data.view;
        }
        else if(data.feedback === -1) // show the error message
        {
          showObject = data.message;
        }
        $('#main').slideDown('slow','swing',function()
        {
          $(this).html(showObject);
        });  
        
        
      },
      error: function(data)
      {
        $('#main').slideDown('slow','swing',function(){$(this).html(xhr.status + ': '+thrownError);});  
      }
      
  }); // ends .ajax
} // getAjaxJSON ends

function tdClassAdd(tBody,tHead,tdWidth) 
{
  var counter = 0;
  var counterMod;
  var tds = $(tBody).children('tr:first').children().length;
  
  
  $(tBody).children('tr').each(function()
  {
    $(this).children('td').each(function()
    {
      counterMod = counter % tds;
      $(this).addClass('td'+counterMod).css({'width':tdWidth[counter]});
      counter = counter + 1;
    });
  });

  counter = 0;
  $(tHead).children('tr').each(function()
  {
    $(this).children('th').each(function()
    {
      counterMod = counter % tds;
      $(this).addClass('td'+counterMod).css({'width':tdWidth[counter]});
      counter = counter + 1;

    });
  });

}

function tableRowSortByColumn(thId)
{
  // row sorting by column
  var th = $(thId);
  var inverse = false; // false means ascending order
  
  th.click(function()
  {
    
    var header = $(this),index = header.index();
    header.closest('table').find('td').filter(function(){
      return $(this).index() === index;
    }).sortElements(function(a, b){ // sortElements function is at global.js
      a = $(a).text();
      b = $(b).text();
      return (isNaN(a) || isNaN(b) ? a.toLowerCase() > b.toLowerCase() : +a > +b) ? inverse ? -1 : 1 :inverse ? 1 : -1;}, 
        function(){return this.parentNode;});
        inverse = !inverse;
    
  });
}  

/**
 http://james.padolsey.com/javascript/sorting-elements-with-jquery/
 * jQuery.fn.sortElements
 * --------------
 * @param Function comparator:
 *   Exactly the same behaviour as [1,2,3].sort(comparator)
 *   
 * @param Function getSortable
 *   A function that should return the element that is
 *   to be sorted. The comparator will run on the
 *   current collection, but you may want the actual
 *   resulting sort to occur on a parent or another
 *   associated element.
 *   
 *   E.g. $('td').sortElements(comparator, function(){
 *      return this.parentNode; 
 *   })
 *   
 *   The <td>'s parent (<tr>) will be sorted instead
 *   of the <td> itself.
 */
jQuery.fn.sortElements = (function(){
 
    var sort = [].sort;
 
    return function(comparator, getSortable) {
 
        getSortable = getSortable || function(){return this;};
 
        var placements = this.map(function(){
 
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
 
                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
 
            return function() {
 
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
 
                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);
 
            };
 
        });
 
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
 
    };
 
})();
  

// common functions end
/************************************************/


// view Page specific functions
/************************************************/

function navbarPhp()
{
  $('#menu').find('a').each(function()
   {
      $(this).bind('click', function(e)
      {
        var url = $(this).attr('href');

        //console.log(url);
        history.pushState(null, null, url);
       
        $(this).parent().parent().children().removeClass('active current');
        $(this).parent().addClass('active current'); 
        
        if(! $(this).parent().hasClass('last'))
        {
          getAjaxJSON(url);
          //e.preventDefault();  
          return false;
        }
        
      });
   });

  $(window).bind('popstate', function()
  {
    getAjaxJSON(location.pathname);
  });
} // navigation bar ends

function forgetPasswordPhp()
{

  // dynamically positioning the password request form and the sign up form
  var bodyWidth =$('body').innerWidth();
  var bodyHeight = $('html').innerHeight();
  var leftWidth = (bodyWidth - 325)/2;
  $('#login').css({
    'marginLeft':leftWidth+'px',
    'marginTop':(bodyHeight/2)+'px'
  });

  /************************************************************************************************************************/
  // manufacturing the password reset request form

  $('#buttonForgetPassword').attr('disabled',true); // make the sign up button disabled, thus it will ensure that there will be no insert without data   
  $('#forgetPasswordForm #userEmail').blur(function()
  {
      var userEmail = $(this).val();
      if(userEmail !== null && userEmail !== '' && userEmail !== undefined)  
        $('#buttonForgetPassword').attr('disabled',false);
    
  }); // $('#forgetPasswordForm #userEmail').blur(function() ends

  $('#forgetPasswordForm').submit(function(eve)
  {
        /* avoiding multiple click over submit button*/
    $('#buttonForgetPassword').attr('disabled',true);

    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
        
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
      
      beforeSend: function()
      {
        $('#forgetPasswordMessage').fadeIn('slow',function()
        {
         $(this).html('');
         $(this).html("Password reset request is being sent ...<img src='/images/indicator.gif'>");
       
        });
      },
      
      success: function(data)
      {
        if(data.feedback !== 0) 
        {
          
          $('#forgetPasswordForm').show();
          $('#forgetPasswordMessage').fadeIn('slow','swing',function()
          {
            $(this).html(data.message);
          });
          $('#forgetPasswordForm fieldset').hide();
        }
        else if(data.feedback === 0)
        {
          $('#forgetPasswordMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
          {
                $(this).html(data.message);
          });  
          $('#buttonForgetPassword').attr('disabled',false);
              
        }
       
      },
      
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#registrationMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonReg').attr('disabled',false);
      }
    }); // .ajax ends
    return false;
  }); // $('#forgetPasswordForm').submit(function(eve) ends  
  
  // manufacturing the the password reset request form ends
  /************************************************************************************************************************/

} // forgetPasswordPhp ends


function loginPhp()
{
  // dynamically positioning the login form and the sign up form
  var bodyWidth =$('body').innerWidth();
  var bodyHeight = $('html').innerHeight();
  var leftWidth = (bodyWidth - 325)/2;
  $('#login').css({
    'marginLeft':leftWidth+'px',
    'marginTop':(bodyHeight/2)+'px'
  });

  // bring up the sign up form
  $('#loginForm fieldset a').bind('click',function()
  {
    $('#loginForm').fadeOut('slow','swing',function()
    {
      $('#registrationMessage').html('');
      $('.verify').html('');
      $('#registrationForm').find("input[type=text], input[type=password]").val(""); // reset form inputs
      $('#registrationForm').fadeIn('slow','swing'); 
  
    });
  }); 


  

  /************************************************************************************************************************/
  // manufacturing the log in form

  $('#loginForm').submit(function(eve)
  {
    eve.preventDefault();
    var formData = $(this).serialize();
    //console.log(formData);
    var urlChange = $(this).attr('action');
    $.ajax({
        url: urlChange,
        type: 'POST',
        data:  formData, //{user_name: userName, password: passWord},
        dataType: 'json',
        beforeSend: function()
        {
          $('#loginMessage').fadeIn('slow',function(){
            $(this).html('User verification ongoing ...<img src=\'/images/indicator.gif\'>');

          });

        },
        success: function(data)
        {
          if(data.success === 0)
          {
            $('#loginMessage').fadeIn('slow',function()
            {
              $(this).html(data.feedback)
              $('#forgetPassword').show();
            });  
          }
          else
          {
            window.location.pathname = data.view; // this will take directly to the data.view location
            
          }
        },
        error: function(data)
        {
          // $('#loginMessage').fadeIn('slow',function(){$(this).html(xhr.status + ': '+thrownError)});
          
        }

      }); // .ajax ends
    }); // ends login form submit

  // manufacturing the log in form ends
  /************************************************************************************************************************/

  // Ajax call to password request form
  /************************************************************************************************************************/  
  $('#forgetPassword').find('a').click(function()
  {
    var url = $(this).attr('href');
    //console.log(url);
    getAjaxJSON(url);
  });
  // Ajax call to password request form ends
  /************************************************************************************************************************/  
  
  /************************************************************************************************************************/
  // manufacturing the sign up form
  
  $('#buttonReg').attr('disabled',true); // make the sign up button disabled, thus it will ensure that there will be no insert without data   
  var buttonDisabledName = 1;
  var buttonDisabledEmail = 1;
  var buttonDisabledPassword =1;
  var buttonDisabledPasswordRetype =1;

  // if any of the above variable is 1, then update button will be disabled

  $('#registrationForm #userName').blur(function() 
  { // verification of userName whether this is unique, minimum 4 characteres long, and maximum 20 characters long
    var userName = $(this).val();
    var userId = 0; 
    $.ajax(
    {
      type: 'POST',
      url: baseUrl+'alveron/checkUser',
      data: {name: userName, id: userId},
      dataType: 'json',
      beforeSend: function()
      {
        $("#registrationMessage").html('');
        $("#registrationMessage").html('User Id Availability checking ... <img src=\'/images/indicator.gif\'>');
      },
      success: function(msg)
      {
        
        if(msg.feedback==1)
        {
          $('#registrationMessage').hide();
          $("#userNameVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
          });
          buttonDisabledName = 0; 
          
          if(buttonDisabledEmail == 0 && buttonDisabledPassword == 0 && buttonDisabledPasswordRetype == 0 )
          {
            $('#buttonReg').attr('disabled',false); // enable the insert button
          } 
          else
          {
            $('#buttonReg').attr('disabled',true);  // disable the insert button
          }

        }
        else
        {
          
          $("#userNameVerify").fadeIn(2000,'swing',function(){
            $(this).html('<img src="/images/no.png">');
            $("#registrationMessage").fadeIn('slow','swing',function()
            {
              $(this).html(''); // empty all previous message
              $(this).html(msg.message);
            });
          });


          buttonDisabledName = 1; 
          //console.log(buttonDisabledName);
          $('#buttonReg').attr('disabled',true);
          
        }             
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#registrationMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonReg').attr('disabled',false);
      }

    }); // ajax ends

  }); // $('#registrationForm #userName').blur(function() ends

  $('#registrationForm #userFirstName').blur(function()
  {  // verification of userFirstName, whether it is not more than 25 characters
    var userFirstName = $(this).val();
    userFirstName = $.trim(userFirstName);

    var $msg;
    if(userFirstName.length > 25)
    {
      $msg = 'Maximum 25 characters will be saved';
      
      $("#userFirstNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#registrationMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
          userFirstName = userFirstName.substr(0,25);
          $('#userFirstName').val(userFirstName);
        });
      });
      
    }
    else
    {
      $('#registrationMessage').hide();
      $("#registrationForm #userFirstNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      
    }
    
  }); // $('#registrationForm #userFirstName').blur(function() ends

  $('#registrationForm #userLastName').blur(function()
  {  // verification of userLastName, whether it is not more than 25 characters
    var userlastName = $(this).val();
    userlastName = $.trim(userlastName);

    var $msg;
    if(userlastName.length > 25)
    {
      $msg = 'Maximum 25 characters will be saved';
      
      $("#userLastNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#registrationMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
          userlastName = userlastName.substr(0,25);
          $('#userLastName').val(userlastName);
        });
      });
      
    }
    else
    {
      $('#registrationMessage').hide();
      $("#registrationForm #userLastNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      
    }
    
  }); // $('#registrationForm #userLastName').blur(function() ends

  $('#registrationForm #userEmail').blur(function()
  {
    var userEmail = $(this).val();
    var userId = 0;

    $.ajax({
      type: 'POST',
      url: baseUrl+'alveron/checkEmail',
      data: {email: userEmail, id: userId},
      dataType: 'json',
      beforeSend: function()
      {
        $("#registrationMessage").html('');
        $("#registrationMessage").html('Email Id uniqueness checking ... <img src=\'/images/indicator.gif\'>');
      },
      success: function(msg)
      {
        $('#registrationMessage').hide();
        if(msg.feedback==1)
        {
          $('#registrationMessage').hide();
          $("#userEmailVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
          }); 
          buttonDisabledEmail = 0; 
          if(buttonDisabledName == 0 && buttonDisabledPassword == 0 && buttonDisabledPasswordRetype == 0 )
          {
            $('#buttonReg').attr('disabled',false); // enable the insert button
          } 
          else
          {
            $('#buttonReg').attr('disabled',true);  // disable the insert button
          }
        }
        else
        {
          $("#userEmailVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/no.png">');
            $("#registrationMessage").fadeIn('slow','swing',function()
            {
              $(this).html(''); // empty all previous message
              $(this).html(msg.message);
            });
          });
          buttonDisabledEmail = 1;
          $('#buttonReg').attr('disabled',true);
           
        }             
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#registrationMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonReg').attr('disabled',false);
      }

    }); // ajax ends
    
  }); // $('#registrationForm #userEmail').blur(function() ends
  
  $('#registrationForm #userPassword').blur(function()
  {
    var userPassword = $(this).val();
    userPassword = $.trim(userPassword);

    var $msg;
    if(userPassword.length < 6 || userPassword.length > 10)
    {
      if(userPassword.length ==0)
      {
        $msg = 'Please enter your desired password';
      }
      else
      {
        $msg = 'Minimum password length is 6, maximum length is 10';
      }
      $("#userPasswordVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#registrationMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
        });
      });
      $("#registrationForm #userPasswordRetype").attr('disabled',true);
      $("#registrationForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('');
      }); // show blank after retype password input box 
      buttonDisabledPassword = 1;
      $('#buttonReg').attr('disabled',true);  // disable the insert button
    }
    else
    {
      $('#registrationMessage').hide();
      $("#registrationForm #userPasswordVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      $("#registrationForm #userPasswordRetype").attr('disabled',false);
      buttonDisabledPassword = 0;
      if(buttonDisabledName == 0 && buttonDisabledEmail == 0 && buttonDisabledPasswordRetype == 0 )
      {
        $('#buttonReg').attr('disabled',false); // enable the insert button
      }
      else
      {
        $('#buttonReg').attr('disabled',true);  // disable the insert button
      }
    }
    
  }); // $('#registrationForm #userPassword').blur(function() ends

  $('#registrationForm #userPasswordRetype').blur(function()
  {
    var userPassword = $('#registrationForm #userPassword').val();
    userPassword = $.trim(userPassword);
    var userPasswordRetype = $(this).val();
    userPasswordRetype = $.trim(userPasswordRetype);
    var $msg;
    
    if(userPasswordRetype !== userPassword)
    {
      $msg = 'Password does not match, try again';
      $("#registrationForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#registrationMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
        });
      });
      buttonDisabledPasswordRetype = 1;
      $('#buttonReg').attr('disabled',true);  // disable the insert button

    }
    else
    {
      $('#registrationMessage').hide();
      $("#registrationForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      buttonDisabledPasswordRetype = 0;
      if(buttonDisabledName == 0 && buttonDisabledEmail == 0 && buttonDisabledPassword == 0 )
      {
        $('#buttonReg').attr('disabled',false); // enable the insert button
      }
      else
      {
        $('#buttonReg').attr('disabled',true);  // disable the insert button
      }
    }
    
  }); // $('#registrationForm #userPasswordRetype').blur(function() ends

  $('#registrationForm').submit(function(eve)
  {
        /* avoiding multiple click over submit button*/
    $('#buttonReg').attr('disabled',true);

    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
        
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
      
      beforeSend: function()
      {
        $('#registrationMessage').fadeIn('slow',function()
        {
         $(this).html('');
         $(this).html("Registration is going on ...<img src='/images/indicator.gif'>");
       
        });
      },
      
      success: function(data)
      {
        if(data.feedback >= 1) // this code will refresh the table after insert successful
        {
          $('#registrationForm').hide();
          $('#loginForm').show();

          $('#loginMessage').fadeIn('slow','swing',function()
          {
            $(this).html(data.message);
          });
          $('#loginForm fieldset').hide();
        }
        else
        {
          $('#registrationMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
          {
                $(this).html(data.message);
          });  
          $('#buttonReg').attr('disabled',false);
              
        }
       
      },
      
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#registrationMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonReg').attr('disabled',false);
      }
    }); // .ajax ends
    return false;
  }); // $('#registrationForm').submit(function(eve) ends
  
  // manufacturing the sign up form ends
  /************************************************************************************************************************/

  

} // loginPhp ends




function userManagementPhp()
{
  
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
    var th = $('#userView').find('thead').find('th');
    tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 

  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
    $('#userView thead').wrap('<div class="theadContainer"></div>');  
    $('#userView tbody').wrap('<div class="tbodyContainer"></div>');
    var tdWidth = ['60','180','180','150','150'];
    
    tdClassAdd('#userView tbody','#userView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

    var tdCount = $('tbody').children('tr:first').children().length;
    var i,sumWidth = 0;
    for(i=0;i<tdCount;i++)
    { 
      sumWidth = sumWidth + parseInt(tdWidth[i]);
    }
    
    var tableWidth = (sumWidth*1.4)+'px';
    $('table.display').css({'width':tableWidth});

    
  
  
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // inline edit of User Status and Type, as well as user Activation
  /************************************************************************************************************************/ 

  // User Status Class td3, user Type class td4

  $('tbody').children('tr').each(function()
  {

    var userId = $(this).attr('id'); // get the userId from tr id
    var userStatusIdNumber = '#userStatus-'+userId; // this is the id of status cell id (.td3)
    $(this).find('.td3').children(userStatusIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var userStatusSelectBoxIdNumber = '#userStatusSelectBox-'+userId;
      var targetSelectBox = targetCell.find(userStatusSelectBoxIdNumber);
      
      
      var targetText = targetCell.find(userStatusIdNumber);
      targetText.hide(); // hide the cell text
      targetSelectBox.show(); // and show the selectBox
      
      targetSelectBox.bind('change',function()
      {
        var selectBoxReference = $(this);
        var userStatusIdChange = $(this).val();
        
        $.ajax(
        {
          url: baseUrl+'alveron/editUserStatus/'+userId+'/'+userStatusIdChange,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideDown('slow',function()
            {
              $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
            });
          },
          success: function(data)
          {
            if(data.feedback === -1)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('You are not allowed to edit user status. Please contact system administrator for further assistance.'); 
              });
            } 
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html(data.feedback);
                //$(this).hide();
              });
              targetText.html(data.userStatusUpdate).show();
              selectBoxReference.hide();  
            }
            

          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }

        }); // ajax ends

      }); // $(targetSelectBox).bind('change',function() ends
      
    }); // $(this).find('.td3 #userStatus-'+userId).click(function() ends

    var userTypeIdNumber = '#userType-'+userId; // this is the id of userType cell id (.td4)
    $(this).find('.td4').children(userTypeIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var userTypeSelectBoxIdNumber = '#userTypeSelectBox-'+userId;
      var targetSelectBox = targetCell.find(userTypeSelectBoxIdNumber);
      
      
      var targetText = targetCell.find(userTypeIdNumber);
      targetText.hide(); // hide the cell text
      targetSelectBox.show(); // and show the selectBox
      
      targetSelectBox.bind('change',function()
      {
        var selectBoxReference = $(this);
        var userTypeIdChange = $(this).val();
        
        $.ajax(
        {
          url: baseUrl+'alveron/editUserType/'+userId+'/'+userTypeIdChange,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
            });
          },
          success: function(data)
          {
            if(data.feedback === -1)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('You are not allowed to edit user type. Please contact system administrator for further assistance.'); 
              });
            } 
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html(data.feedback);
                //$(this).hide();

              });
              targetText.html(data.userTypeUpdate).show();
              selectBoxReference.hide();  
            }
            

          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }

        }); // ajax ends

      }); // $(targetSelectBox).bind('change',function() ends
      
    }); // $(this).find('.td4 #usertype-'+userId).click(function() ends

  }); // $(tBody).children('tr').each(function() ends

   // inline edit of User Status and Type ends, as well as user activation
  /************************************************************************************************************************/ 
} // userManagementPhp ends


function userTypeManagementPhp()
{

  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
    var th = $('#userTypeView').find('thead').find('th');
    tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
    $('#userTypeView thead').wrap('<div class="theadContainer"></div>');  
    $('#userTypeView tbody').wrap('<div class="tbodyContainer"></div>');
    var tdWidth = ['120','80','80'];
    
    tdClassAdd('#userTypeView tbody','#userTypeView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

    var tdCount = $('tbody').children('tr:first').children().length;
    var i,sumWidth = 0;
    for(i=0;i<tdCount;i++)
    { 
      sumWidth = sumWidth + parseInt(tdWidth[i]);
    }
    
    var tableWidth = (sumWidth*1.4)+'px';
    $('table.display').css({'width':tableWidth});

    
    
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  
  
  // inline edit of User Type Name
  /************************************************************************************************************************/ 

  // User Type Class td0

  $('tbody').children('tr').each(function()
  {

    var userTypeId = $(this).attr('id'); // get the userTypeId from tr id
    var userTypeTextIdNumber = '#userTypeText-'+userTypeId; // this is the id of status cell id (.td0)
    $(this).find('.td0').children(userTypeTextIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var userTypeInputIdNumber = '#userTypeInput-'+userTypeId;
      var userTypeInput = targetCell.find(userTypeInputIdNumber);
      
      
      var userTypeText = targetCell.find(userTypeTextIdNumber);
      userTypeText.hide(); // hide the cell text
      userTypeInput.show(); // and show the input box
      
      userTypeInput.blur(function()
      {
        var textInputReference = $(this);

        var userTypeChange = textInputReference.val();


        $.ajax(
        {
          url: baseUrl+'alveron/checkUserTypeName/'+userTypeId+'/'+userTypeChange,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html("User Type Name uniqueness checking is going on ...<img src='/images/indicator.gif'>");
            });  
          },
          success:function(dataUnique)
          {
            if(dataUnique.feedback === 1)
            {
              $.ajax(
              {
                url: baseUrl+'alveron/editUserTypeName/'+userTypeId+'/'+userTypeChange,
                type: 'POST',
                dataType: 'json',
                beforeSend: function()
                {
                  $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                  {
                    $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
                  });

                },
                success: function(data)
                {
                  if(data.feedback === -1)
                  {
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                       $(this).html('You are not allowed to edit user type name. Please contact system administrator for further assistance.'); 
                    });
                  } 
                  else
                  {
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html(data.feedback);
                      //$(this).hide();
                    });
                    userTypeText.text(data.userTypeUpdate).show();
                    //textInputReference.html(data.userTypeUpdate).hide();
                    textInputReference.hide();  
                  } 
                  

                },
                error: function (xhr, ajaxOptions, thrownError) 
                {
                                
                    $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                    {
                        $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                    }); 
                }

              }); // ajax ends      
            }
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                  {
                    $(this).html("User Type Name already exists, please choose a different name ...<img src='/images/indicator.gif'>");
                  });              
            }
          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }
        }); // uniqueness check of user type name through ajax ends
        
        

      }); // userTypeInput.blur(function() ends
      
    }); //$(this).find('.td0').children(userTypeText).click(function() ends

    
  }); // $(tBody).children('tr').each(function() ends

  // inline edit of User Type Name ends
  /************************************************************************************************************************/  

  // New User Type
  /************************************************************************************************************************/  

  $('#userTypeAdd').click(function()
  {
    $(this).hide();
    $('#userTypeCreate').show();
    $('#userTypeCreate').blur(function()
    {
      var userType = $(this).val();
      var userTypeId = 0;
      $.ajax(
        {
          url: baseUrl+'alveron/checkUserTypeName/'+userTypeId+'/'+userType,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html("User Type Name uniqueness checking is going on ...<img src='/images/indicator.gif'>");
            });  
          },
          success:function(dataUnique)
          {
            if(dataUnique.feedback === 1)
            {
              $.ajax(
              {
                url: baseUrl+'alveron/addUserTypeName/'+userType,
                type: 'POST',
                dataType: 'json',
                beforeSend: function()
                {
                  $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                  {
                    $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
                  });

                },
                success: function(data)
                {
                  if(data.feedback === -1)
                  {
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                       $(this).html('You are not allowed to add user type name. Please contact system administrator for further assistance.'); 
                    });
                  } 
                  else
                  {
                    $('#main').html(data.view);
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html(data.feedback);
                      
                    });
                    $('#userTypeAdd').show();
                    $('#userTypeCreate').hide();
                   } 
                },
                error: function (xhr, ajaxOptions, thrownError) 
                {
                                
                    $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                    {
                        $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                    }); 
                }

              }); // ajax ends      
            }
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                  {
                    $(this).html("User Type Name already exists, please choose a different name ...<img src='/images/indicator.gif'>");
                  });              
            }
          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }
        }); // uniqueness check of user type name through ajax ends

    }); // $('#userTypeCreate').blur(function() ends
  }); // $('#userTypeAdd').click(function() ends

  // New User Type ends
  /************************************************************************************************************************/  
} // userTypeManagementPhp() ends


function userTypeAccessManagementPhp()
{
 
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
    var th = $('#userTypeAccessView').find('thead').find('th');
    tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 

  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
    $('#userTypeAccessView thead').wrap('<div class="theadContainer"></div>');  
    $('#userTypeAccessView tbody').wrap('<div class="tbodyContainer"></div>');
    var tdWidth = ['200','250','100'];
    
    tdClassAdd('#userTypeAccessView tbody','#userTypeAccessView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

    var tdCount = $('tbody').children('tr:first').children().length;
    var i,sumWidth = 0;
    for(i=0;i<tdCount;i++)
    { 
      sumWidth = sumWidth + parseInt(tdWidth[i]);
    }
    
    var tableWidth = (sumWidth*1.4)+'px';
    $('table.display').css({'width':tableWidth});

    
    
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 


  

  // inline edit of User Type Access
  /************************************************************************************************************************/ 

  // User Type Class td2

  $('tbody').children('tr').each(function()
  {

    var userTypeAccessId = $(this).attr('id'); // get the userTypeAccessId from tr id
    var userTypeAccessIdNumber = '#userTypeAccessText-'+userTypeAccessId; // this is the id of user type access cell id (.td2)
    $(this).find('.td2').children(userTypeAccessIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var userTypeAccessSelectBoxIdNumber = '#userTypeAccessSelectBox-'+userTypeAccessId;
      var targetSelectBox = targetCell.find(userTypeAccessSelectBoxIdNumber);
      
      
      var targetText = targetCell.find(userTypeAccessIdNumber);
      targetText.hide(); // hide the cell text
      targetSelectBox.show(); // and show the selectBox
      
      targetSelectBox.bind('change',function()
      {
        var selectBoxReference = $(this);
        var userTypeAccessIdChange = $(this).val();
        
        $.ajax(
        {
          url: baseUrl+'alveron/editUserTypeAccess/'+userTypeAccessId+'/'+userTypeAccessIdChange,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
            });
          },
          success: function(data)
          {
            if(data.feedback === -1)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('You are not allowed to edit user type access. Please contact system administrator for further assistance.'); 
              });
            } 
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html(data.feedback);
                //$(this).hide();
              });
              targetText.html(data.userTypeAccessValueUpdate).show();
              selectBoxReference.hide();  
            }
            

          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }

        }); // ajax ends

      }); // $(targetSelectBox).bind('change',function() ends
      
    }); // $(this).find('.td2 #userTypeAccessText-'+userId).click(function() ends

    
  }); // $(tBody).children('tr').each(function() ends

   // inline edit of User Status and Type ends, as well as user activation
  /************************************************************************************************************************/ 
  
  // search option
  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    $('#hiddenUserTypeSearch').hide();
    $('#hiddenUserActionSearch').hide();

    $.ajax(
    {
      url: baseUrl+'alveron/userTypeAccessManagement',
      type: 'POST',
      dataType: 'json',
      success: function(data)
      {
        $('#main').html(data.view);
        if(searchOption == 1) // search by userType
        {
          $('#hiddenUserTypeSearch').show(); // show the user type search select box
        }
        else if(searchOption == 2) // search by actionName
        {
          $('#hiddenUserActionSearch').show(); // show the action page search select box
        }
        $('#searchOption').val(searchOption);
      }
    });

    
  });
  // search option ends

  // search by userType
  $('#userTypeSearch').bind('change',function()
  {
    var userTypeId = $(this).val();
    $.ajax(
    {
      url: baseUrl+'alveron/userTypeAccessManagement/'+userTypeId+'/0',
      type: 'POST',
      dataType: 'json',
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html("Page loading ...<img src='/images/indicator.gif'>");
        });
      },
      success: function(data)
      {
        $('#main').html(data.view);
        $('#hiddenUserTypeSearch').show(); // show the user type search select box
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
                      
          $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
          {
              $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
          }); 
          
      }
    });
  });
  // search by userType ends

  // search by userAction
  $('#userActionSearch').bind('change',function()
  {
    var userActionId = $(this).val();
    $.ajax(
    {
      url: baseUrl+'alveron/userTypeAccessManagement/0/'+userActionId,
      type: 'POST',
      dataType: 'json',
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html("Page loading ...<img src='/images/indicator.gif'>");
        });
      },
      success: function(data)
      {
        $('#main').html(data.view);
        $('#hiddenUserActionSearch').show(); // show the action page search select box
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
                      
          $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
          {
              $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
          }); 
          
      }
    });
  });
  // search by userAction ends

  // New User Type Access
  /************************************************************************************************************************/  

  $('#userTypeAccessAddImg').click(function()
  {
    
    $(this).hide();
    $('#userTypeAccessCreate').show();
    
    // populating action list
    $('#userTypeAccessAdd').attr('disabled',true); // make the submit button disabled, by default
    $('#userTypeNew').bind('change',function()
    {
      var userTypeId = $(this).val();
      if(userTypeId>0)
      {
        $.ajax(
        {
          url: baseUrl+'alveron/actionListForUserTypeAccessManagement',
          type: 'POST',
          data: {userTypeId:userTypeId},
          dataType: 'json',
          success: function(data)
          {
            $('#hiddenSelect').html(data.htmlBody);
            if(data.feedback > 0)
              $('#userTypeAccessAdd').attr('disabled',false); // enable submit button, if list is populated
          }
        });
      }
    });
    // populating action list ends
    $('#userTypeAccessCreate').submit(function(eve)
    {
      
      $('#userTypeAccessAdd').attr('disabled',true); /* avoiding multiple click over submit button, button Id is userTypeAccessAdd */
      
      var formData = $(this).serialize();
      var urlChange = $(this).attr('action');

      $.ajax(
      {
        url: urlChange,
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function()
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow',function()
          {
            $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
          });

        },
        success: function(data)
        {
          if(data.feedback === -1)
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
               $(this).html('You are not allowed to add user type access. Please contact system administrator for further assistance.'); 
            });
            $('#userTypeAccessAdd').attr('disabled',false);
          } 

          else
          {

            $('#main').html(data.view);
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html(data.feedback);
              
            });
            $('#userTypeAccessAddImg').show();
            $('#userTypeAccessAdd').attr('disabled',false);
            $('#userTypeAccessCreate').hide();
          } 
        },
        error: function (xhr, ajaxOptions, thrownError) 
        {
                        
            $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
            {
                $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
            }); 
            $('#userTypeAccessAdd').attr('disabled',false);
        }
      }); // ajax ends
      eve.preventDefault();  
    }); // $('#userTypeAccessCreate').submit(function(eve) ends
  }); // $('#userTypeAccessAddImg').click(function() ends

  // New User Type Access ends
  /************************************************************************************************************************/  


} // userTypeAccessManagementPhp ends


function userPasswordResetPhp()
{
  
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
    var th = $('#userPasswordResetView').find('thead').find('th');
    tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 

  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
    $('#userPasswordResetView thead').wrap('<div class="theadContainer"></div>');  
    $('#userPasswordResetView tbody').wrap('<div class="tbodyContainer"></div>');
    var tdWidth = ['60','130','150','80','150','50'];
    
    tdClassAdd('#userPasswordResetView tbody','#userPasswordResetView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

    var tdCount = $('tbody').children('tr:first').children().length;
    var i,sumWidth = 0;
    for(i=0;i<tdCount;i++)
    { 
      sumWidth = sumWidth + parseInt(tdWidth[i]);
    }
    
    var tableWidth = (sumWidth*1.4)+'px';
    $('table.display').css({'width':tableWidth});

    
  
  
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // User Password Reset Submit
  /************************************************************************************************************************/ 

  
  $('.reset a').click(function(eve)
  {

    eve.preventDefault();
    
    var referenceButton = $(this);
    var url = $(this).attr('href');
    $.ajax(
    {
      url: url,
      type: 'GET',
      dataType: 'json',
      beforeSend: function()
      {
        $('.oprtMessage').slideUp('fast').slideDown('slow',function()
        {
          $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
        });
      },
      success: function(data)
      {
        if(data.feedback)
        {
          if(data.feedback === -1)
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
               $(this).html('You are not allowed to edit user status. Please contact system administrator for further assistance.'); 
            });
          } 
          
          else if (data.feedback >= 1)
          {
            referenceButton.hide();
            $('#main').html(data.view);
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
               $(this).html('Password is reset, an email has been sent to the user.'); 
               //$(this).html('Password is reset, an email has been sent to the user. New password is '+data.pwd); 
            }); 
          }  
          else if(data.feedback === 0)
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
               $(this).html('Some error occurs. Please try again'); 
            });   
          }
        }
        else
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow',function()
          {
             $(this).html('Some error occurs. Please try again'); 
          });
        }

      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
                      
          $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
          {
              $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
          }); 
      }

    }); // ajax ends
   

  }); // $('.reset a').click(function() ends

  // User Password Reset Submit Ends
  /************************************************************************************************************************/ 
  // populate admin password reset form
  /************************************************************************************************************************/ 
  
  $('#adminPasswordReset').bind('click',function()
  {
    //console.log('here');
    $(this).hide();
    
    $('#adminPasswordResetForm').show();
    $('#userIdNew').val(0);
    $('#adminPasswordResetForm').find("input[type=text]").val("");
    $('#buttonAdminPasswordReset').attr('disabled',false);

    // admin Password Reset Submit 
    /************************************************************************************************************************/ 
    $('#buttonAdminPasswordReset').submit(function(ev)
    {
      
      //eve.preventDefault();
      $('#buttonAdminPasswordReset').attr('disabled',true); /* avoiding multiple click over submit button, button Id is buttonAdminPasswordReset */
        
      var formData = $(this).serialize();
      var urlChange = $(this).attr('action');
      $.ajax(
      {
        url: urlChange,
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend:function()
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
          {
            $(this).html("Prcess is going on ... <img src='/images/indicator.gif'>");
          });
        },
        success: function(data)
        {

            if (data.feedback >= 1)
            {
              
              //$('#main').html(data.view);
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('Password is reset, an email has been sent to the user.'); 
                 //$(this).html('Password is reset, an email has been sent to the user. New password is '+data.pwd); 
              }); 
              $('#adminPasswordResetForm').hide();
              $('#adminPasswordReset').show();
            }  
            else if(data.feedback === 0)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('Some error occurs. Please try again'); 
              });   
            }
            else if(data.feedback === -1)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('Either no user selected, or no password given'); 
              }); 
            }
        }
      });
      ev.preventDefault(); 
      //return false;

    });

    // admin Password Reset Submit Ends
    /************************************************************************************************************************/ 
 
  });

  // populate admin password reset form ends
  /************************************************************************************************************************/ 
  

} // userPasswordResetPhp ends


function accountManagementPhp()
{
  
  // dynamically positioning the login form and the sign up form
  var bodyWidth =$('body').innerWidth();
  var bodyHeight = $('html').innerHeight();
  var leftWidth = (bodyWidth - 325)/2;
  $('#login').css({
    'marginLeft':leftWidth+'px',
    'marginTop':(bodyHeight/2)+'px'
  });

  $('#buttonAccountManagement').attr('disabled',true); // make the sign up button disabled, thus it will ensure that there will be no insert without data   
  var buttonDisabledName = 0;
  var buttonDisabledOldPassword = 1;
  var buttonDisabledNewPassword =1;
  var buttonDisabledPasswordRetype =1;

  // if any of the above variable is 1, then update button will be disabled

  $('#accountManagementForm #userName').blur(function() 
  { // verification of userName whether this is unique, minimum 4 characteres long, and maximum 20 characters long
    var userName = $(this).val();
    var userId = $(this).parent().parent().parent().find('.userIdReference').attr('id'); 
    console.log('userId '+userId);
    $.ajax(
    {
      type: 'POST',
      url: baseUrl+'alveron/checkUser',
      data: {name: userName, id: userId},
      dataType: 'json',
      beforeSend: function()
      {
        $("#accountManagementMessage").html('');
        $("#accountManagementMessage").html('User Id Availability checking ... <img src=\'/images/indicator.gif\'>');
      },
      success: function(msg)
      {
        
        if(msg.feedback==1)
        {
          $('#accountManagementMessage').hide();
          $("#userNameVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
          });
          buttonDisabledName = 0; 
          
          if(buttonDisabledOldPassword == 0 && buttonDisabledNewPassword == 0 && buttonDisabledPasswordRetype == 0 )
          {
            $('#buttonAccountManagement').attr('disabled',false); // enable the insert button
          } 
          else
          {
            $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button
          }

        }
        else
        {
          
          $("#userNameVerify").fadeIn(2000,'swing',function(){
            $(this).html('<img src="/images/no.png">');
            $("#accountManagementMessage").fadeIn('slow','swing',function()
            {
              $(this).html(''); // empty all previous message
              $(this).html(msg.message);
            });
          });


          buttonDisabledName = 1; 
          //console.log(buttonDisabledName);
          $('#buttonAccountManagement').attr('disabled',true);
          
        }             
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#accountManagementMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonAccountManagement').attr('disabled',false);
      }

    }); // ajax ends

  }); // $('#accountManagementForm #userName').blur(function() ends

  $('#accountManagementForm #userFirstName').blur(function()
  {  // verification of userFirstName, whether it is not more than 25 characters
    var userFirstName = $(this).val();
    userFirstName = $.trim(userFirstName);

    var $msg;
    if(userFirstName.length > 25)
    {
      $msg = 'Maximum 25 characters will be saved';
      
      $("#userFirstNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#accountManagementMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
          userFirstName = userFirstName.substr(0,25);
          $('#userFirstName').val(userFirstName);
        });
      });
      
    }
    else
    {
      $('#accountManagementMessage').hide();
      $("#accountManagementForm #userFirstNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      
    }
    
  }); // $('#accountManagementForm #userFirstName').blur(function() ends

  $('#accountManagementForm #userLastName').blur(function()
  {  // verification of userLastName, whether it is not more than 25 characters
    var userlastName = $(this).val();
    userlastName = $.trim(userlastName);

    var $msg;
    if(userlastName.length > 25)
    {
      $msg = 'Maximum 25 characters will be saved';
      
      $("#userLastNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#accountManagementMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
          userlastName = userlastName.substr(0,25);
          $('#userLastName').val(userlastName);
        });
      });
      
    }
    else
    {
      $('#accountManagementMessage').hide();
      $("#accountManagementForm #userLastNameVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      
    }
    
  }); // $('#accountManagementForm #userLastName').blur(function() ends

  
  $('#accountManagementForm #userOldPassword').blur(function() 
  { // verification of userPassword whether this is unique, minimum 4 characteres long, and maximum 20 characters long
    var userOldPassword = $(this).val();
    var userId = $(this).parent().parent().parent().find('.userIdReference').attr('id'); 
    //console.log('userId '+userId);
    $.ajax(
    {
      type: 'POST',
      url: baseUrl+'alveron/checkPassword',
      data: {password: userOldPassword, id: userId},
      dataType: 'json',
      beforeSend: function()
      {
        $("#accountManagementMessage").html('');
        $("#accountManagementMessage").html('User Password checking ... <img src=\'/images/indicator.gif\'>');
      },
      success: function(msg)
      {
        
        if(msg.feedback==1) // 1 means old password matches
        {
          $('#accountManagementMessage').hide();
          $("#userOldPasswordVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
          });
          buttonDisabledOldPassword = 0; 
          
          if(buttonDisabledName == 0 && buttonDisabledNewPassword == 0 && buttonDisabledPasswordRetype == 0 )
          {
            $('#buttonAccountManagement').attr('disabled',false); // enable the insert button
          } 
          else
          {
            $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button
          }

        }
        else
        {
          
          $("#userOldPasswordVerify").fadeIn(2000,'swing',function(){
            $(this).html('<img src="/images/no.png">');
            $("#accountManagementMessage").fadeIn('slow','swing',function()
            {
              $(this).html(''); // empty all previous message
              $(this).html(msg.message);
            });
          });


          buttonDisabledOldPassword = 1; 
          //console.log(buttonDisabledName);
          $('#buttonAccountManagement').attr('disabled',true);
          
        }             
      },
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#accountManagementMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonAccountManagement').attr('disabled',false);
      }

    }); // ajax ends

  }); // $('#accountManagementForm #userOldPassword').blur(function() ends


  $('#accountManagementForm #userNewPassword').blur(function()
  {
    var userPassword = $(this).val();
    userPassword = $.trim(userPassword);

    var $msg;
    if(userPassword.length < 6 || userPassword.length > 10)
    {
      if(userPassword.length ==0)
      {
        $msg = 'Please enter your desired password';
      }
      else
      {
        $msg = 'Minimum password length is 6, maximum length is 10';
      }
      $("#userNewPasswordVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#accountManagementMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
        });
      });
      $("#accountManagementForm #userPasswordRetype").attr('disabled',true);
      $("#accountManagementForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('');
      }); // show blank after retype password input box 
      buttonDisabledNewPassword = 1;
      $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button
    }
    else
    {
      $('#accountManagementMessage').hide();
      $("#accountManagementForm #userNewPasswordVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      $("#accountManagementForm #userPasswordRetype").attr('disabled',false);
      buttonDisabledNewPassword = 0;
      if(buttonDisabledName == 0 && buttonDisabledOldPassword == 0 && buttonDisabledPasswordRetype == 0 )
      {
        $('#buttonAccountManagement').attr('disabled',false); // enable the insert button
      }
      else
      {
        $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button
      }
    }
    
  }); // $('#accountManagementForm #userNewPassword').blur(function() ends

  $('#accountManagementForm #userPasswordRetype').blur(function()
  {
    var userPassword = $('#accountManagementForm #userNewPassword').val();
    userPassword = $.trim(userPassword);
    
    var userPasswordRetype = $(this).val();
    userPasswordRetype = $.trim(userPasswordRetype);
    var $msg;
    
    if(userPasswordRetype !== userPassword)
    {
      $msg = 'Password does not match, try again';
      $("#accountManagementForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/no.png">');
        $("#accountManagementMessage").fadeIn('slow','swing',function()
        {
          $(this).html(''); // empty all previous message
          $(this).html($msg);
        });
      });
      buttonDisabledPasswordRetype = 1;
      $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button

    }
    else
    {
      $('#accountManagementMessage').hide();
      $("#accountManagementForm #userPasswordRetypeVerify").fadeIn(2000,'swing',function()
      {
        $(this).html('<img src="/images/yes.png">');
      }); 
      buttonDisabledPasswordRetype = 0;
      if(buttonDisabledName == 0 && buttonDisabledOldPassword == 0 && buttonDisabledNewPassword == 0 )
      {
        $('#buttonAccountManagement').attr('disabled',false); // enable the insert button
      }
      else
      {
        $('#buttonAccountManagement').attr('disabled',true);  // disable the insert button
      }
    }
    
  }); // $('#accountManagementForm #userPasswordRetype').blur(function() ends

  $('#accountManagementForm').submit(function(eve)
  {
    eve.preventDefault();
        /* avoiding multiple click over submit button*/
    $('#buttonAccountManagement').attr('disabled',true);

    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
        
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
      
      beforeSend: function()
      {
        $('#accountManagementMessage').fadeIn('slow',function()
        {
         $(this).html('');
         $(this).html("Update is going on ...<img src='/images/indicator.gif'>");
       
        });
      },
      
      success: function(data)
      {
        if(data.feedback >= 1) 
        {
                    

          $('#accountManagementMessage').fadeOut(1000,'swing').fadeIn('slow','swing',function()
          {
            $(this).html(data.message);
          });
          
        }
        else
        {
          $('#accountManagementMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
          {
              $(this).html(data.message);
          });  
          $('#buttonAccountManagement').attr('disabled',false);
              
        }

        $('.verify').html('');
        $('#accountManagementForm').find("input[type=password]").val(""); // clear form passwords

        $('#buttonAccountManagement').attr('disabled',true);
        $('#userPasswordRetype').attr('disabled',true);
      },
      
      error: function (xhr, ajaxOptions, thrownError) 
      {
              
        $('#accountManagementMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });  
        $('#buttonAccountManagement').attr('disabled',false);

        $('.verify').html('');
        $('#accountManagementForm').find("input[type=password]").val(""); // clear form passwords

        $('#buttonAccountManagement').attr('disabled',true);
        $('#userPasswordRetype').attr('disabled',true);

      }
    }); // .ajax ends
    
  }); // $('#accountManagementForm').submit(function(eve) ends

} // accountManagementPhp() ends


function carrierManagementPhp()
{
  
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
    var th = $('#carrierManagementView').find('thead').find('th');
    tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 

  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
    $('#carrierManagementView thead').wrap('<div class="theadContainer"></div>');  
    $('#carrierManagementView tbody').wrap('<div class="tbodyContainer"></div>');
    var tdWidth = ['120','120','120','60'];
    
    tdClassAdd('#carrierManagementView tbody','#carrierManagementView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

    var tdCount = $('tbody').children('tr:first').children().length;
    var i,sumWidth = 0;
    for(i=0;i<tdCount;i++)
    { 
      sumWidth = sumWidth + parseInt(tdWidth[i]);
    }
    
    var tableWidth = (sumWidth*1.4)+'px';
    $('table.display').css({'width':tableWidth});

    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 
  //search option
  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    //first hide all search options
    $('#hiddenCarrierTypeSearch').hide();
    $('#hiddenTrafficTypeSearch').hide();
    $('#hiddenCarrierStatusSearch').hide();
    
    $.ajax(
    {
      url: baseUrl+'alveron/carrierManagement',
      type: 'POST',
      dataType: 'json',
      success: function(data)
      {
        $('#main').html(data.view);
        //based on searchOption value, show respective search Option
        if(searchOption == 1)
        {
          $('#hiddenCarrierTypeSearch').show();
        }  
        else if(searchOption == 2)
        {
          $('#hiddenTrafficTypeSearch').show();
        }
        else if(searchOption == 3)
        {
          $('#hiddenCarrierStatusSearch').show();  
        }
        $('#searchOption').val(searchOption);
      }
    });
    
    

  });
  
  // search option ends

  // search by carrier Type
  $('#carrierTypeSearch').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    
    $.ajax(
    {
      url: baseUrl+'alveron/carrierManagement/0/'+carrierTypeId+'/0/-1',
      type: 'POST',
      dataType: 'json',
      success: function(data)
      {
        $('#main').html(data.view);
        $('#hiddenCarrierTypeSearch').show();
      }
    });
    
  });
  // search by carrier Type ends

  // search by traffic type
  $('#trafficTypeSearch').bind('change',function()
  {
    var trafficTypeId = $(this).val();
    $.ajax(
    {
      url: baseUrl+'alveron/carrierManagement/0/0/'+trafficTypeId+'/-1',
      type: 'POST',
      dataType: 'json',
      success: function(data)
      {
        $('#main').html(data.view);
        $('#hiddenTrafficTypeSearch').show();
      }
    });
  });
  // search by traffic type ends

  // search by carrier status
  
  $('#carrierStatusSearch').bind('change',function()
  {
    var carrierStatusId = $(this).val();
    $.ajax(
    {
      url: baseUrl+'alveron/carrierManagement/0/0/0/'+carrierStatusId,
      type: 'POST',
      dataType: 'json',
      success: function(data)
      {
        $('#main').html(data.view);
        $('#hiddenCarrierStatusSearch').show();
      }
    });
  });
  
  // search by carrier status ends

  // inline edit 
  /************************************************************************************************************************/ 

  // Carrier Status Class td3 

  $('tbody').children('tr').each(function()
  {

    var carrierId = $(this).attr('id'); // get the carrierId from tr id
    
    var carrierStatusIdNumber = '#carrierStatus-'+carrierId; // this is the id of carrier type cell id (.td0)
    
    // edit carrierTypeId
    $(this).find('.td3').children(carrierStatusIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var carrierStatusSelectBoxIdNumber = '#carrierStatusSelectBox-'+carrierId;
      var targetSelectBox = targetCell.find(carrierStatusSelectBoxIdNumber);
      
      
      var targetText = targetCell.find(carrierStatusIdNumber);
      targetText.hide(); // hide the cell text
      targetSelectBox.show(); // and show the selectBox
      
      targetSelectBox.bind('change',function()
      {
        var selectBoxReference = $(this);
        var carrierStatusIdChange = $(this).val();
        
        $.ajax(
        {
          url: baseUrl+'alveron/editCarrierStatusId/'+carrierId+'/'+carrierStatusIdChange,
          type: 'POST',
          dataType: 'json',
          beforeSend: function()
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
            });
          },
          success: function(data)
          {
            if(data.feedback === -1)
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                 $(this).html('You are not allowed to edit carrier status. Please contact system administrator for further assistance.'); 
              });
            } 
            else
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html(data.feedback);
                //$(this).hide();
              });
              targetText.html(data.carrierStatusUpdateId).show();
              selectBoxReference.hide();  
            }
            

          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
                          
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                  $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
          }

        }); // ajax ends

      }); // $(targetSelectBox).bind('change',function() ends
      
    }); // $(this).find('.td3 #carrierStatus-'+carrierId).click(function() ends

    
    

  }); // $(tBody).children('tr').each(function() ends

   // inline edit ends
  /************************************************************************************************************************/ 

  // New Carrier Activate
  /************************************************************************************************************************/  

  $('#carrierActivateAdd').click(function()
  {
    
    $(this).hide();
    $('#carrierActivateAddForm').show();
    $('#buttonCarrierActivateAdd').attr('disabled',false); // make the submit button enabled, by default

    
    $('#carrierActivateAddForm').submit(function(eve)  // when submit button is clicked
    {
      
      eve.preventDefault();  
      $('#buttonCarrierActivateAdd').attr('disabled',true); /* avoiding multiple click over submit button, button Id is userTypeAccessAdd */
      
      var formData = $(this).serialize();
      var urlChange = $(this).attr('action');

      $.ajax(
      {
        url: urlChange,
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function()
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow',function()
          {
            $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
          });

        },
        success: function(data)
        {
          if(data.feedback === -1)
          {
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
               $(this).html('You are not allowed to activate carrier. Please contact system administrator for further assistance.'); 
            });
            $('#buttonCarrierActivateAdd').attr('disabled',false);
          } 

          else
          {

            $('#main').html(data.view);
            $('.oprtMessage').slideUp('fast').slideDown('slow',function()
            {
              //console.log(data.feedback);
              $(this).html(data.feedback);
              
            });
            $('#carrierActivateAdd').show();
            $('#buttonCarrierActivateAdd').attr('disabled',false);
            $('#carrierActivateAddForm').hide();
          } 
        },
        error: function (xhr, ajaxOptions, thrownError) 
        {
                        
            $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
            {
                $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
            }); 
            $('#buttonCarrierActivateAdd').attr('disabled',false);
        }
      }); // ajax ends
      
    }); // $('#carrierActivateAddForm').submit(function(eve) ends
  }); // $('#carrierActivateAdd').click(function() ends

  // New Carrier Activate ends
  /************************************************************************************************************************/  
} // carrierManagementPhp ends


function carrierListPhp()
{

  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#carrierListView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#carrierListView thead').wrap('<div class="theadContainer"></div>');  
  $('#carrierListView tbody').wrap('<div class="tbodyContainer"></div>');
  var tdWidth = ['120','300'];
  
  tdClassAdd('#carrierListView tbody','#carrierListView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.4)+'px';
  $('table.display').css({'width':tableWidth});

    
    
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  
  
  // inline edit of carrier Name, carrier Description
  /************************************************************************************************************************/ 

  // carrier Name td0, carrier Description td1

  $('tbody').children('tr').each(function()
  {

    var carrierNameId = $(this).attr('id'); // get the carrierNameId from tr id
    var carrierNameTextIdNumber = '#carrierNameText-'+carrierNameId; // this is the id of carrier name cell id (.td0)
    var carrierDescriptionTextIdNumber = '#carrierDescriptionText-'+carrierNameId; // this is the id of carrier description cell id (.td1)
    
    $(this).find('.td0').children(carrierNameTextIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var carrierNameInputIdNumber = '#carrierNameInput-'+carrierNameId;
      var carrierNameInput = targetCell.find(carrierNameInputIdNumber);
      
      
      var carrierNameText = targetCell.find(carrierNameTextIdNumber);
      var carrierNameOld = carrierNameText.val();
      carrierNameText.hide(); // hide the cell text
      carrierNameInput.show(); // and show the input box
      
      carrierNameInput.blur(function()
      {
        var textInputReference = $(this);

        var carrierNameChange = textInputReference.val();
        carrierNameChange = $.trim(carrierNameChange);
        if(carrierNameChange !== '' && carrierNameChange !== null && carrierNameChange !== undefined)
        {
          $.ajax(
          {
            url: baseUrl+'alveron/checkCarrierName/'+carrierNameId+'/'+carrierNameChange,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html("Carrier Name uniqueness checking is going on ...<img src='/images/indicator.gif'>");
              });  
            },
            success:function(dataUnique)
            {
              if(dataUnique.feedback === 1)
              {
                $.ajax(
                {
                  url: baseUrl+'alveron/editCarrierName/'+carrierNameId+'/'+carrierNameChange,
                  type: 'POST',
                  dataType: 'json',
                  beforeSend: function()
                  {
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
                    });

                  },
                  success: function(data)
                  {
                    if(data.feedback === -1)
                    {
                      $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                      {
                         $(this).html('You are not allowed to edit carrier name. Please contact system administrator for further assistance.'); 
                      });
                    } 
                    else
                    {
                      $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                      {
                        $(this).html(data.feedback);
                        //$(this).hide();
                      });
                      carrierNameText.text(data.carrierNameUpdate).show();
                      //textInputReference.html(data.userTypeUpdate).hide();
                      textInputReference.hide();  
                    } 
                    

                  },
                  error: function (xhr, ajaxOptions, thrownError) 
                  {
                                  
                    $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                    {
                        $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                    }); 
                  }

                }); // ajax ends of carrier name update     
              }
              else
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html("Carrier Name already exists, please choose a different name ...<img src='/images/indicator.gif'>");
                    });              
              }
            }, // success function of carrierName uniqueness ajax
            error: function (xhr, ajaxOptions, thrownError) 
            {
                            
                $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                {
                    $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                }); 
                carrierNameText.val(carrierNameOld).show(); // show the cell text
                carrierNameInput.hide(); // and hide the input box

            }
          }); // uniqueness check of carrier name through ajax ends
        }
        else // if carrier name is empty
        {
          $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
          {
              $(this).html('Carrier Name can not be empty');//.slideUp(2000,'swing');
          }); 
          carrierNameText.val(carrierNameOld).show(); // show the cell text
          carrierNameInput.hide(); // and hide the input box 
        }
      }); // carrierNameInput.blur(function() ends
      
    }); //$(this).find('.td0').children(carrierNameTextIdNumber).click(function() ends

    $(this).find('.td1').children(carrierDescriptionTextIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var carrierDescriptionInputIdNumber = '#carrierDescriptionInput-'+carrierNameId;
      var carrierDescriptionInput = targetCell.find(carrierDescriptionInputIdNumber);
      
      
      var carrierDescriptionText = targetCell.find(carrierDescriptionTextIdNumber);
      var carrierDescriptionOld = carrierDescriptionText.val();
      carrierDescriptionText.hide(); // hide the cell text
      carrierDescriptionInput.show(); // and show the input box
        
      carrierDescriptionInput.blur(function()
      {
        var textInputReference = $(this);

        var carrierDescriptionChange = textInputReference.val();

        carrierDescriptionChange = $.trim(carrierDescriptionChange);
        if(carrierDescriptionChange !== '' && carrierDescriptionChange !== null && carrierDescriptionChange !== undefined)
        {
          $.ajax(
          {
            url: baseUrl+'alveron/editCarrierDescription/'+carrierNameId+'/'+carrierDescriptionChange,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
              });

            },
            success: function(data)
            {
              if(data.feedback === -1)
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                {
                   $(this).html('You are not allowed to edit carrier description. Please contact system administrator for further assistance.'); 
                });
              } 
              else
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                {
                  $(this).html(data.feedback);
                  //$(this).hide();
                });
                carrierDescriptionText.text(data.carrierDescriptionUpdate).show();
                //textInputReference.html(data.userTypeUpdate).hide();
                textInputReference.hide();  
              } 
            },
            error: function (xhr, ajaxOptions, thrownError) 
            {
                              
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                    $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
              carrierDescriptionText.val(carrierDescriptionOld).hide(); // show the cell text
              carrierDescriptionInput.show(); // and hide the input box

            }

          }); // ajax ends      
        }
        else // if empty
        {
          $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
          {
                $(this).html('Description can not be eumpty');//.slideUp(2000,'swing');
          }); 
          carrierDescriptionText.val(carrierDescriptionOld).hide(); // show the cell text
          carrierDescriptionInput.show(); // and hide the input box

        }
      }); // carrierDescriptionInput.blur(function() ends

    }); //$(this).find('.td1').children(carrierDescriptionText).click(function() ends

      
  }); // $(tBody).children('tr').each(function() ends

    // inline edit of carrier Name and Carrier Description ends
    /************************************************************************************************************************/  

    // New Carrier
    /************************************************************************************************************************/  

    $('#carrierAdd').click(function()
    {
      var buttonDisabledName = 1;
      var buttonDisabledDescription = 1;
      $('#buttonCarrierAdd').attr('disabled',true);
      // if both above is true, then button is disabled
      $(this).hide();
      $('#carrierAddForm').show();
      $('.oprtMessage').hide();
      
      // refresh insert form
      $('#carrierDescriptionNew').html('');
      $('#carrierNameNew').html('');
      // refresh insert form ends
      
      $('#carrierDescriptionNew').blur(function()
      {
        var carrierDescription = $(this).val();
        carrierDescription = $.trim(carrierDescription);
        
        if(carrierDescription == null || carrierDescription == '' || carrierDescription == undefined)
        {
          $(".oprtMessage").fadeIn('slow','swing',function()
          {
            $(this).html(''); // empty all previous message
            $(this).html('Description can not be empty');
          });

          $("#carrierDescriptionVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/no.png">');
            
          });
          $('#buttonCarrierAdd').attr('disabled',true); // disable the insert button   
          buttonDisabledDescription = 1;
        }  
        else
        {
          $('.oprtMessage').fadeOut('fast');
          $("#carrierDescriptionVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
            
          });
          buttonDisabledDescription = 0;
          if(buttonDisabledName == 0)
            $('#buttonCarrierAdd').attr('disabled',false); // enable the insert button   
          else
            $('#buttonCarrierAdd').attr('disabled',true); // disable the insert button   
        }
      });

      $('#carrierNameNew').blur(function()
      {
        var carrierName = $(this).val();
        var carrierNameId = 0;
        carrierName = $.trim(carrierName);
        if(carrierName !== '' && carrierName !== null && carrierName !== undefined)
        {
          $.ajax(
          {
            url: baseUrl+'alveron/checkCarrierName/'+carrierNameId+'/'+carrierName,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html("Carrier Name uniqueness checking is going on ...<img src='/images/indicator.gif'>");
              });  
            },
            success:function(data)
            {
              if(data.feedback==1)
              {
                $('.oprtMessage').hide();
                $("#carrierNameVerify").fadeIn(2000,'swing',function()
                {
                  $(this).html('<img src="/images/yes.png">');
                });
                
                buttonDisabledName = 0;

                if(buttonDisabledDescription == 0)
                  $('#buttonCarrierAdd').attr('disabled',false); // enable the insert button   
                else
                  $('#buttonCarrierAdd').attr('disabled',true); // disable the insert button 
              }
              else
              {
              
                $(".oprtMessage").fadeIn('slow','swing',function()
                {
                  $(this).html(''); // empty all previous message
                  $(this).html(msg.message);
                });

                $("#carrierNameVerify").fadeIn(2000,'swing',function()
                {
                  $(this).html('<img src="/images/no.png">');
                  
                });
                buttonDisabledName = 1;
                $('#buttonCarrierAdd').attr('disabled',true); // disable the insert button   
                         
              }
            },
            error: function(xhr,ajaxOptions,thrownError) 
            {
                    
              $('.oprtMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
              {
                $(this).html(xhr.status + ': '+thrownError);
              });  
              $("#carrierNameVerify").fadeIn(2000,'swing',function()
              {
                $(this).html('<img src="/images/no.png">');
                
              });
              buttonDisabledName = 1;
              $('#buttonCarrierNameAdd').attr('disabled',true);
            }

          }); // ajax ends 
        }
        else
        {
          $('.oprtMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
          {
            $(this).html('Carrier Name can not be empty');
          });  
          $("#carrierNameVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/no.png">');
            
          });
          buttonDisabledName = 1;
          $('#buttonCarrierNameAdd').attr('disabled',true);
        }

              
      }); // $('#carrierNameNew').blur(function() ends


    }); // $('#carrierAdd').click(function() ends


    $('#carrierAddForm').submit(function(eve)
    {
      /* avoiding multiple click over submit button*/
      $('#buttonCarrierAdd').attr('disabled',true);

      var formData = $(this).serialize();
      var urlChange = $(this).attr('action');
        
      $.ajax(
      {
        url: urlChange,
        type: 'POST',
        data:  formData, //{user_name: userName, password: passWord},
        dataType: 'json',
      
        beforeSend: function()
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
          {
            $(this).html('');
            $(this).html("New carrier is being added ...<img src='/images/indicator.gif'>");
          });
        },
      
        success: function(data)
        {
          $('#main').html(data.view);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.feedback);    
          });
          $('#buttonCarrierAdd').attr('disabled',true);
          $('#carrierAdd').show();
          $('#carrierAddForm').hide();
        },
      
        error: function (xhr, ajaxOptions, thrownError) 
        {
              
          $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
          {
            $(this).html(xhr.status + ': '+thrownError);
          });
          $('#buttonCarrierAdd').attr('disabled',false);
        }
      }); // .ajax ends
      return false;
    }); // $('#carrierAddForm').submit(function(eve) ends  
  

    // New Carrier ends
    /************************************************************************************************************************/  
} // carrierListPhp() ends

function trafficTypeListPhp()
{

  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#trafficTypeListView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#trafficTypeListView thead').wrap('<div class="theadContainer"></div>');  
  $('#trafficTypeListView tbody').wrap('<div class="tbodyContainer"></div>');
  var tdWidth = ['120','300'];
  
  tdClassAdd('#trafficTypeListView tbody','#trafficTypeListView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.4)+'px';
  $('table.display').css({'width':tableWidth});

    
    
    
  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  
  
  // inline edit of traffic Type, traffic Description
  /************************************************************************************************************************/ 

  // traffic Type td0, traffic Description td1

  $('tbody').children('tr').each(function()
  {

    var trafficTypeId = $(this).attr('id'); // get the trafficTypeId from tr id
    var trafficTypeTextIdNumber = '#trafficTypeText-'+trafficTypeId; // this is the id of traffic type cell id (.td0)
    var trafficDescriptionTextIdNumber = '#trafficDescriptionText-'+trafficTypeId; // this is the id of traffic description cell id (.td1)
    
    $(this).find('.td0').children(trafficTypeTextIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var trafficTypeInputIdNumber = '#trafficTypeInput-'+trafficTypeId;
      var trafficTypeInput = targetCell.find(trafficTypeInputIdNumber);
      
      
      var trafficTypeText = targetCell.find(trafficTypeTextIdNumber);
      var trafficTypeTextOld = trafficTypeText.val();
      trafficTypeText.hide(); // hide the cell text
      trafficTypeInput.show(); // and show the input box
      
      trafficTypeInput.blur(function()
      {
        var textInputReference = $(this);

        var trafficTypeChange = textInputReference.val();
        trafficTypeChange = $.trim(trafficTypeChange);
        if(trafficTypeChange !== '' && trafficTypeChange !== null && trafficTypeChange !== undefined)
        {
          $.ajax(
          {
            url: baseUrl+'alveron/checkTrafficType/'+trafficTypeId+'/'+trafficTypeChange,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html("Traffic Type uniqueness checking is going on ...<img src='/images/indicator.gif'>");
              });  
            },
            success:function(dataUnique)
            {
              if(dataUnique.feedback === 1)
              {
                $.ajax(
                {
                  url: baseUrl+'alveron/editTrafficType/'+trafficTypeId+'/'+trafficTypeChange,
                  type: 'POST',
                  dataType: 'json',
                  beforeSend: function()
                  {
                    $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
                    });

                  },
                  success: function(data)
                  {
                    if(data.feedback === -1)
                    {
                      $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                      {
                         $(this).html('You are not allowed to edit traffic type. Please contact system administrator for further assistance.'); 
                      });
                    } 
                    else
                    {
                      $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                      {
                        //$(this).html(data.feedback);
                        $(this).hide();
                      });
                      trafficTypeText.text(data.trafficTypeUpdate).show();
                      //textInputReference.html(data.userTypeUpdate).hide();
                      textInputReference.hide();  
                    } 
                    

                  },
                  error: function (xhr, ajaxOptions, thrownError) 
                  {
                                  
                    $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                    {
                        $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                    }); 
                  }

                }); // ajax ends of traffic type update     
              }
              else
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                    {
                      $(this).html("Traffic Type already exists, please choose a different name ...<img src='/images/indicator.gif'>");
                    });              
              }
            }, // success function of carrierName uniqueness ajax
            error: function (xhr, ajaxOptions, thrownError) 
            {
                            
                $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
                {
                    $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
                }); 
                trafficTypeText.val(trafficTypeTextOld).show(); // show the cell text
                trafficTypeInput.hide(); // and hide the input box
            }
          }); // uniqueness check of traffic type through ajax ends
        }
        else // if traffic type is empty
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html('Traffic type can not be empty');
          });

          trafficTypeText.val(trafficTypeTextOld).show(); // show the cell text
          trafficTypeInput.hide(); // and hide the input box
        }

      }); // trafficTypeInput.blur(function() ends
      
    }); //$(this).find('.td0').children(trafficTypeTextIdNumber).click(function() ends

    $(this).find('.td1').children(trafficDescriptionTextIdNumber).click(function()
    {
      
      var targetCell = $(this).parent(); // this is the reference to the target td
      var targetRow = $(this).parent().parent(); // this is the reference to the target tr
            

      var trafficDescriptionInputIdNumber = '#trafficDescriptionInput-'+trafficTypeId;
      var trafficDescriptionInput = targetCell.find(trafficDescriptionInputIdNumber);
      
      
      var trafficDescriptionText = targetCell.find(trafficDescriptionTextIdNumber);
      var trafficDescriptionOld = trafficDescriptionText.val();
      trafficDescriptionText.hide(); // hide the cell text
      trafficDescriptionInput.show(); // and show the input box
        
      trafficDescriptionInput.blur(function()
      {
        var textInputReference = $(this);

        var trafficDescriptionChange = textInputReference.val();

        trafficDescriptionChange = $.trim(trafficDescriptionChange);
        if(trafficDescriptionChange !== '' && trafficDescriptionChange !== null && trafficDescriptionChange !== undefined)
        {
          $.ajax(
          {
            url: baseUrl+'alveron/editTrafficDescription/'+trafficTypeId+'/'+trafficDescriptionChange,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideDown('slow',function()
              {
                $(this).html("Process is going on ...<img src='/images/indicator.gif'>");
              });

            },
            success: function(data)
            {
              if(data.feedback === -1)
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                {
                   $(this).html('You are not allowed to edit traffic description. Please contact system administrator for further assistance.'); 
                });
              } 
              else
              {
                $('.oprtMessage').slideUp('fast').slideDown('slow',function()
                {
                  $(this).html(data.feedback);
                  //$(this).hide();
                });
                trafficDescriptionText.text(data.trafficDescriptionUpdate).show();
                //textInputReference.html(data.userTypeUpdate).hide();
                textInputReference.hide();  
              } 
            },
            error: function (xhr, ajaxOptions, thrownError) 
            {
                              
              $('.oprtMessage').slideUp('fast').slideDown(2000,'swing',function()
              {
                    $(this).html(xhr.status + ': '+thrownError);//.slideUp(2000,'swing');
              }); 
            }

          }); // ajax ends                
        }
        else // if description is empty
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function(){
            $(this).html('Description can not be empty');
          });
          trafficDescriptionText.val(trafficDescriptionOld).show(); // show the cell text
          trafficDescriptionInput.hide(); // and hide the input box

        }
      }); // trafficDescriptionInput.blur(function() ends

    }); //$(this).find('.td1').children(trafficDescriptionText).click(function() ends

      
  }); // $(tBody).children('tr').each(function() ends

    // inline edit of Traffic Type and Traffic Description ends
    /************************************************************************************************************************/  

    // New Traffic Type
    /************************************************************************************************************************/  

    $('#trafficTypeAdd').click(function()
    {
      var buttonDisabledType = 1;
      var buttonDisabledDescription = 1;
      $('#buttontrafficTypeAdd').attr('disabled',true);
      // both above value is 1, then submit button is disbled

      $(this).hide();
      $('#trafficTypeAddForm').show();
      $('#trafficDescriptionNew').blur(function()
      {
        var trafficDescription = $(this).val();
        trafficDescription = $.trim(trafficDescription);
        if(trafficDescription == null || trafficDescription == '' || trafficDescription == undefined) // if description is empty
        {
          $(".oprtMessage").fadeIn('slow','swing',function()
          {
            $(this).html(''); // empty all previous message
            $(this).html('Description can not be empty');
          });

          $("#trafficDescriptionVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/no.png">');
            
          });
          $('#buttonTrafficTypeAdd').attr('disabled',true); // disable the insert button   
          buttonDisabledDescription = 1;
        }  
        else
        {
          $('.oprtMessage').fadeOut('fast');
          $("#trafficDescriptionVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/yes.png">');
            
          });
          buttonDisabledDescription = 0;
          if(buttonDisabledType == 0)
            $('#buttonTrafficTypeAdd').attr('disabled',false); // enable the insert button   
          else
            $('#buttonTrafficTypeAdd').attr('disabled',true); // disable the insert button   
        }
      });

      $('#trafficTypeNew').blur(function()
      {
        var trafficType = $(this).val();
        trafficType = $.trim(trafficType);
        var trafficTypeId = 0;
        if(trafficType !== '' && trafficType !== null && trafficType !== undefined )
        {
          $.ajax(
          {
            url: baseUrl+'alveron/checkTrafficType/'+trafficTypeId+'/'+trafficType,
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
              $('.oprtMessage').slideUp('fast').slideDown('slow',function()
              {
                $(this).html("Traffic Type uniqueness checking is going on ...<img src='/images/indicator.gif'>");
              });  
            },
            success:function(data)
            {
              if(data.feedback==1)
              {
                $('.oprtMessage').hide();
                $("#trafficTypeVerify").fadeIn(2000,'swing',function()
                {
                  $(this).html('<img src="/images/yes.png">');
                });
                buttonDisabledType = 0;
                if(buttonDisabledDescription == 0)
                  $('#buttonTrafficTypeAdd').attr('disabled',false); // enable the insert button   
                else
                  $('#buttonTrafficTypeAdd').attr('disabled',true); // disable the insert button   
              }
              else
              {
              
                $(".oprtMessage").fadeIn('slow','swing',function()
                {
                  $(this).html(''); // empty all previous message
                  $(this).html(msg.message);
                });

                $("#trafficTypeVerify").fadeIn(2000,'swing',function()
                {
                  $(this).html('<img src="/images/no.png">');
                  
                });
                buttonDisabledType = 1;
                $('#buttonTrafficTypeAdd').attr('disabled',true); // disable the insert button   
                         
              }
            },
            error: function(xhr,ajaxOptions,thrownError) 
            {
                    
              $('.oprtMessage').fadeOut(1000,'swing').fadeIn(1000,'swing',function()
              {
                $(this).html(xhr.status + ': '+thrownError);
              });  
              $("#trafficTypeVerify").fadeIn(2000,'swing',function()
              {
                $(this).html('<img src="/images/no.png">');
                
              });
              $('#buttonTrafficTypeAdd').attr('disabled',false);
            }

          }); // ajax ends           
        }        
        else // if trafficType is empty
        {
          $(".oprtMessage").fadeIn('slow','swing',function()
          {
            $(this).html(''); // empty all previous message
            $(this).html('Traffic type can not be empty');
          });

          $("#trafficTypeVerify").fadeIn(2000,'swing',function()
          {
            $(this).html('<img src="/images/no.png">');
            
          });
          buttonDisabledType = 1;
          $('#buttonTrafficTypeAdd').attr('disabled',true); // disable the insert button   

        }

              
      }); // $('#trafficTypeNew').blur(function() ends


    }); // $('#trafficTypeAdd').click(function() ends


    $('#trafficTypeAddForm').submit(function(eve)
    {
      /* avoiding multiple click over submit button*/
      $('#buttonTrafficTypeAdd').attr('disabled',true);

      var formData = $(this).serialize();
      var urlChange = $(this).attr('action');

        
      $.ajax(
      {
        url: urlChange,
        type: 'POST',
        data:  formData, //{user_name: userName, password: passWord},
        dataType: 'json',
      
        beforeSend: function()
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
          {
            $(this).html('');
            $(this).html("New traffic type is being added ...<img src='/images/indicator.gif'>");
          });
        },
      
        success: function(data)
        {
          $('#main').html(data.view);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.feedback);    
          });
          $('#buttonTrafficTypeAdd').attr('disabled',true);
          $('#trafficTypeAdd').show();
          $('#trafficTypeAddForm').hide();
        },
      
        error: function (xhr, ajaxOptions, thrownError) 
        {
              
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(xhr.status + ': '+thrownError);
          });
          $('#buttonTrafficTypeAdd').attr('disabled',false);
        }
      }); // .ajax ends
      return false;
    }); // $('#trafficTypeAddForm').submit(function(eve) ends  
  

    // New Traffic Type ends
    /************************************************************************************************************************/  
} // trafficTypeListPhp() ends

function rateUploadPhp()
{
  $('#rateUploadForm').submit(function(e)
  {
    
    $('#buttonRateUpload').attr('disabled',true);
    e.preventDefault();
    urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajaxFileUpload(
    {
      url: urlChange,
      secureuri: false,
      fileElementId: 'userfile',
      dataType: 'json',
      data: {'carrierId':$('#carrierId').val()},
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html("Rate chart upload is going on, please wait ...<img src='/images/indicator.gif'>");
        });
      },
      success: function (data, status)
      {

        if( data.msg !== 'success' ) // if file upload is successful, then data.error will be ''
        {
          //console.log(data.error);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.error); // from controller alveron.php $error will provide this data
          });  

        }
        else
        {
          //console.log(data.msg);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.error);
          });
        }
        $('#buttonRateUpload').attr('disabled',false);
      },
      error: function (data, status, e)
      {
        //console.log(e);
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html(e);
        });
        $('#buttonRateUpload').attr('disabled',false);
      }
    }); // ajaxFileUpload() ends
    return false;      
  }); //  $('#rateUploadForm').submit(function(e) ends
} // rateUploadPhp ends

function specialRateUploadPhp()
{
  $('#specialRateUploadForm').submit(function(e)
  {
    
    $('#buttonSpecialRateUpload').attr('disabled',true);
    e.preventDefault();
    urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajaxFileUpload(
    {
      url: urlChange,
      secureuri: false,
      fileElementId: 'userfile',
      dataType: 'json',
      data: {'carrierId':$('#carrierId').val()},
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html("Rate chart upload is going on, please wait ...<img src='/images/indicator.gif'>");
        });
      },
      success: function (data, status)
      {

        if( data.msg !== 'success' ) // if file upload is successful, then data.error will be ''
        {
          //console.log(data.error);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.error); // from controller alveron.php $error will provide this data
          });  

        }
        else
        {
          //console.log(data.msg);
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(data.error);
          });
        }
        $('#buttonSpecialRateUpload').attr('disabled',false);
      },
      error: function (data, status, e)
      {
        //console.log(e);
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html(e);
        });
        $('#buttonSpecialRateUpload').attr('disabled',false);
      }
    }); // ajaxFileUpload() ends
    return false;      
  }); //  $('#rateUploadForm').submit(function(e) ends
} // specialRateUploadPhp ends


function getCarrierListForRateCompare(carrierTypeId,trafficTypeId)
{
  //console.log('here getCarrierList');
  $.ajax(
  {
    url: baseUrl+'alveron/carrierListForRateCompare',
    type: 'POST',
    data: {carrierTypeId: carrierTypeId, trafficTypeId: trafficTypeId},
    dataType: 'json',
    beforeSend: function(){},
    success: function(data)
    {
      $('#hiddenSelect').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html(data.htmlBody);
      });
      if(data.feedback === 1)
      {
        $('#buttonRateSearch').attr('disabled',false); // enable the search button
      }
      else
      {
        $('#buttonRateSearch').attr('disabled',true); // disable the search button 
      }
    },
    error: function()
    {

    }
  }); // ajax ends
}

function rateComparePhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#rateCompareView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#rateCompareView thead').wrap('<div class="theadContainer"></div>');  
  $('#rateCompareView tbody').wrap('<div class="tbodyContainer"></div>');
  var columnCount = $('#columnCount').html();
  var tdWidth = ['250','250','150'];
  
  for( var columnLoopCount = 0; columnLoopCount < columnCount-3;columnLoopCount++)
  {
    tdWidth.push('150');
    tdWidth.push('150');
  }  
  
  
  tdClassAdd('#rateCompareView tbody','#rateCompareView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});

  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // manufacturing search form
  /************************************************************************************************************************/ 

  
  // dateTimePicker
  $('#searchDateTime').datetimepicker(
  {
    format: 'Y-m-d H:i'
  });  
  // dateTimePicker ends

  // show carrier list if both carrierTypeId and trafficTypeId are non-zero
  if($('#carrierTypeId').val() > 0 && $('#trafficTypeId').val() > 0)
  {
    getCarrierListForRateCompare($('#carrierTypeId').val(),$('#trafficTypeId').val());
  }

  // change action on carrierTypeId select box
  $('#carrierTypeId').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var trafficTypeId = $('#trafficTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForRateCompare(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  // change action on carrierTypeId select box
  $('#trafficTypeId').bind('change',function()
  {
    var trafficTypeId = $(this).val();
    var carrierTypeId = $('#carrierTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForRateCompare(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  $('#rateSearchForm').submit(function(e)
  {
   
    $('#buttonRateSearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Rate comparison report is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to put value either in Country, or in Destination, or in Prefix field");
          });
        }
        else if(data.feedback == 2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to select at least one partner");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
        }
        
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonRateSearch').attr('disabled',false);
      }
    }); // .ajax ends
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
 
} // rateComparePhp ends


function getCarrierListForSpecialRateCompare(carrierTypeId,trafficTypeId)
{
  //console.log('here getCarrierList');
  $.ajax(
  {
    url: baseUrl+'alveron/carrierListForSpecialRateCompare',
    type: 'POST',
    data: {carrierTypeId: carrierTypeId, trafficTypeId: trafficTypeId},
    dataType: 'json',
    beforeSend: function(){},
    success: function(data)
    {
      $('#hiddenSelect').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html(data.htmlBody);
      });
      if(data.feedback === 1)
      {
        $('#buttonRateSearch').attr('disabled',false); // enable the search button
      }
      else
      {
        $('#buttonRateSearch').attr('disabled',true); // disable the search button 
      }
    },
    error: function()
    {

    }
  }); // ajax ends
} // getCarrierListForSpecialRateCompare(carrierTypeId,trafficTypeId)

function specialRateComparePhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#rateCompareView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#rateCompareView thead').wrap('<div class="theadContainer"></div>');  
  $('#rateCompareView tbody').wrap('<div class="tbodyContainer"></div>');
  var columnCount = $('#columnCount').html();
  var tdWidth = ['250','250','150'];
  
  for( var columnLoopCount = 0; columnLoopCount < columnCount-3;columnLoopCount++)
  {
    tdWidth.push('150'); // Rate
    tdWidth.push('150'); // ASR
    tdWidth.push('150'); // ACD
    tdWidth.push('150'); // Effective Date
  }  
  
  
  tdClassAdd('#rateCompareView tbody','#rateCompareView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});

  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // manufacturing search form
  /************************************************************************************************************************/ 

  
  // dateTimePicker
  $('#searchDateTime').datetimepicker(
  {
    format: 'Y-m-d H:i'
  });  
  // dateTimePicker ends

  // show carrier list if both carrierTypeId and trafficTypeId are non-zero
  if($('#carrierTypeId').val() > 0 && $('#trafficTypeId').val() > 0)
  {
    getCarrierListForSpecialRateCompare($('#carrierTypeId').val(),$('#trafficTypeId').val());
  }

  // change action on carrierTypeId select box
  $('#carrierTypeId').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var trafficTypeId = $('#trafficTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForSpecialRateCompare(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  // change action on carrierTypeId select box
  $('#trafficTypeId').bind('change',function()
  {
    var trafficTypeId = $(this).val();
    var carrierTypeId = $('#carrierTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForSpecialRateCompare(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  $('#rateSearchForm').submit(function(e)
  {
   
    $('#buttonRateSearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Rate comparison report is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to put value either in Country, or in Destination, or in Prefix field");
          });
        }
        else if(data.feedback == 2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to select at least one partner");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
        }
        
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonRateSearch').attr('disabled',false);
      }
    }); // .ajax ends
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
 
} // specialRateComparePhp ends

function getCarrierListForPartnerRateHistory(carrierTypeId,trafficTypeId)
{
  //console.log('here getCarrierList');
  $.ajax(
  {
    url: baseUrl+'alveron/carrierListForPartnerRateHistory',
    type: 'POST',
    data: {carrierTypeId: carrierTypeId, trafficTypeId: trafficTypeId},
    dataType: 'json',
    beforeSend: function(){},
    success: function(data)
    {
      $('#hiddenSelect').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html(data.htmlBody);
      });
      if(data.feedback === 1)
      {
        $('#buttonPartnerRateHistorySearch').attr('disabled',false); // enable the search button
      }
      else
      {
        $('#buttonPartnerRateHistorySearch').attr('disabled',true); // disable the search button 
      }
    },
    error: function()
    {

    }
  }); // ajax ends
}

function partnerRateHistoryPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#partnerRateHistoryView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#partnerRateHistoryView thead').wrap('<div class="theadContainer"></div>');  
  $('#partnerRateHistoryView tbody').wrap('<div class="tbodyContainer"></div>');
  
  var columnCount = $('#columnCount').html();
  var tdWidth = ['250','250','100'];
  
  for( var columnLoopCount = 0; columnLoopCount < columnCount-3;columnLoopCount++)
  {
    tdWidth.push('180');
    
  }  
   
  tdClassAdd('#partnerRateHistoryView tbody','#partnerRateHistoryView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
  $('table caption').css({'width':tableWidth, 'fontWeight':'bold'});

  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // manufacturing search form
  /************************************************************************************************************************/ 

  
  // dateTimePicker
  $('#searchDateTimeFrom').datetimepicker(
  {
    format: 'Y-m-d',
    timepicker:false
  });  
  $('#searchDateTimeTo').datetimepicker(
  {
    format: 'Y-m-d',
    timepicker:false
  });  
  // dateTimePicker ends

  // show carrier list if both carrierTypeId and trafficTypeId are non-zero
  if($('#carrierTypeId').val() > 0 && $('#trafficTypeId').val() > 0)
  {
    getCarrierListForPartnerRateHistory($('#carrierTypeId').val(),$('#trafficTypeId').val());
  }

  // change action on carrierTypeId select box
  $('#carrierTypeId').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var trafficTypeId = $('#trafficTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForPartnerRateHistory(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  // change action on carrierTypeId select box
  $('#trafficTypeId').bind('change',function()
  {
    var trafficTypeId = $(this).val();
    var carrierTypeId = $('#carrierTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForPartnerRateHistory(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  $('#partnerRateHistorySearchForm').submit(function(e)
  {
    
    $('#buttonPartnerRateHistorySearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Partner Rate History report is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to put value either in Country, or in Destination, or in Prefix field");
          });
        }
        else if(data.feedback == 2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to select at least one partner");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonPartnerRateHistorySearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
        }
        
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonPartnerRateHistorySearch').attr('disabled',false);
      }
    }); // .ajax ends
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
} // partnerRateHistoryPhp ends


function getCarrierListForPartnerSpecialRateHistory(carrierTypeId,trafficTypeId)
{
  //console.log('here getCarrierList');
  $.ajax(
  {
    url: baseUrl+'alveron/carrierListForPartnerSpecialRateHistory',
    type: 'POST',
    data: {carrierTypeId: carrierTypeId, trafficTypeId: trafficTypeId},
    dataType: 'json',
    beforeSend: function(){},
    success: function(data)
    {
      $('#hiddenSelect').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html(data.htmlBody);
      });
      if(data.feedback === 1)
      {
        $('#buttonPartnerRateHistorySearch').attr('disabled',false); // enable the search button
      }
      else
      {
        $('#buttonPartnerRateHistorySearch').attr('disabled',true); // disable the search button 
      }
    },
    error: function()
    {

    }
  }); // ajax ends
}

function partnerSpecialRateHistoryPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#partnerRateHistoryView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#partnerRateHistoryView thead').wrap('<div class="theadContainer"></div>');  
  $('#partnerRateHistoryView tbody').wrap('<div class="tbodyContainer"></div>');
  
  var columnCount = $('#columnCount').html();
  var tdWidth = ['250','250','100'];
  
  for( var columnLoopCount = 0; columnLoopCount < columnCount-3;columnLoopCount++)
  {
    tdWidth.push('180');
    tdWidth.push('180');
    tdWidth.push('180');
  }  
   
  tdClassAdd('#partnerRateHistoryView tbody','#partnerRateHistoryView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
  $('table caption').css({'width':tableWidth, 'fontWeight':'bold'});

  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // manufacturing search form
  /************************************************************************************************************************/ 

  
  // dateTimePicker
  $('#searchDateTimeFrom').datetimepicker(
  {
    format: 'Y-m-d',
    timepicker:false
  });  
  $('#searchDateTimeTo').datetimepicker(
  {
    format: 'Y-m-d',
    timepicker:false
  });  
  // dateTimePicker ends

  // show carrier list if both carrierTypeId and trafficTypeId are non-zero
  if($('#carrierTypeId').val() > 0 && $('#trafficTypeId').val() > 0)
  {
    getCarrierListForPartnerRateHistory($('#carrierTypeId').val(),$('#trafficTypeId').val());
  }

  // change action on carrierTypeId select box
  $('#carrierTypeId').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var trafficTypeId = $('#trafficTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForPartnerSpecialRateHistory(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  // change action on carrierTypeId select box
  $('#trafficTypeId').bind('change',function()
  {
    var trafficTypeId = $(this).val();
    var carrierTypeId = $('#carrierTypeId').val();
    //console.log('carrierTypeId = '+carrierTypeId);
    //console.log('trafficTypeId = '+trafficTypeId);
    if(carrierTypeId > 0 && trafficTypeId > 0)
      getCarrierListForPartnerSpecialRateHistory(carrierTypeId,trafficTypeId);
  }); // change action on carrierTypeId select box ends

  $('#partnerRateHistorySearchForm').submit(function(e)
  {
    
    $('#buttonPartnerRateHistorySearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Partner Rate History report is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to put value either in Country, or in Destination, or in Prefix field");
          });
        }
        else if(data.feedback == 2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("You need to select at least one partner");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonPartnerRateHistorySearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
        }
        
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonPartnerRateHistorySearch').attr('disabled',false);
      }
    }); // .ajax ends
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
} // partnerSpecialRateHistoryPhp ends



function rateChartCustomSearch(carrierId,country,prefix)
{
  $.ajax(
  {
    url: baseUrl+'alveron/partnerRateChart',
    type: 'post',
    data: {carrierId:carrierId,country:country,prefix:prefix},
    dataType: 'json',
    beforeSend: function()
    {
      $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html('Rate chart uploading ...<img src=\'/images/indicator.gif\'>');

      });
    },
    success: function(data)
    {
      
       $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("Select a partner");
          });
        }
        else if(data.feedback == -2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("No rate chart found");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateChartSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
          
        }
        

      $('#hiddenSearchOption').show();
      if(country != '')
        $('#hiddenCountrySearch').show();
      else if(prefix != '')
        $('#hiddenPrefixSearch').show();
    }
  });
}

function partnerRateChartPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#partnerRateChartView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#partnerRateChartView thead').wrap('<div class="theadContainer"></div>');  
  $('#partnerRateChartView tbody').wrap('<div class="tbodyContainer"></div>');
 
  var tdWidth = ['250','250','80','60','150'];
  
 
  
  tdClassAdd('#partnerRateChartView tbody','#partnerRateChartView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
  $('table caption').css({'width':tableWidth, 'fontWeight':'bold'});



  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // inline search 
  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    //console.log(searchOption);
    
    $('#hiddenCountrySearch').hide();
    $('#hiddenPrefixSearch').hide();

    if(searchOption == 1)
    {
      $('#hiddenCountrySearch').show();
      $('#hiddenCountrySearch').find("input[type=text]").val(""); // reset form inputs

    }
    else if(searchOption == 2)
    {
      $('#hiddenPrefixSearch').show(); 
      $('#hiddenPrefixSearch').find("input[type=text]").val(""); // reset form inputs
    }
  });

  $('#country').blur(function()
  {
    var country = $(this).val();
    var prefix = '';
    var carrierId = $('.carrierId').attr('id');
    rateChartCustomSearch(carrierId,country,prefix);
  });

  $('#prefix').blur(function()
  {
    var prefix = $(this).val();
    var country = '';
    var carrierId = $('.carrierId').attr('id');
    rateChartCustomSearch(carrierId,country,prefix);
  });

  // inline search ends

  // manufacturing search form
  /************************************************************************************************************************/ 
  $('#buttonRateChartSearch').attr('disabled',false);
  $('#rateChartSearchForm').submit(function(e)
  {
    
    $('#buttonRateChartSearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Rate Chart is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("Select a partner");
          });
        }
        else if(data.feedback == -2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("No rate chart found");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateChartSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
          $('#hiddenSearchOption').show();
        }
        //if(data.showSearchInput == 1)
          //$('#hiddenSearchOption').show();
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonRateChartSearch').attr('disabled',false);
      }
    }); // .ajax ends
    return false;
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
 
} // partnerRateChartPhp ends


function specialRateChartCustomSearch(carrierId,country,prefix)
{
  $.ajax(
  {
    url: baseUrl+'alveron/partnerSpecialRateChart',
    type: 'post',
    data: {carrierId:carrierId,country:country,prefix:prefix},
    dataType: 'json',
    beforeSend: function()
    {
      $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html('Rate chart uploading ...<img src=\'/images/indicator.gif\'>');

      });
    },
    success: function(data)
    {
      
       $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("Select a partner");
          });
        }
        else if(data.feedback == -2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("No chart found");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateChartSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
          
        }
        

      $('#hiddenSearchOption').show();
      if(country != '')
        $('#hiddenCountrySearch').show();
      else if(prefix != '')
        $('#hiddenPrefixSearch').show();
    }
  });
}


function partnerSpecialRateChartPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#partnerRateChartView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#partnerRateChartView thead').wrap('<div class="theadContainer"></div>');  
  $('#partnerRateChartView tbody').wrap('<div class="tbodyContainer"></div>');
 
  var tdWidth = ['250','250','80','60','80','80','150'];
  
 
  
  tdClassAdd('#partnerRateChartView tbody','#partnerRateChartView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
  $('table caption').css({'width':tableWidth, 'fontWeight':'bold'});



  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // inline search 
  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    //console.log(searchOption);
    
    $('#hiddenCountrySearch').hide();
    $('#hiddenPrefixSearch').hide();

    if(searchOption == 1)
    {
      $('#hiddenCountrySearch').show();
      $('#hiddenCountrySearch').find("input[type=text]").val(""); // reset form inputs

    }
    else if(searchOption == 2)
    {
      $('#hiddenPrefixSearch').show(); 
      $('#hiddenPrefixSearch').find("input[type=text]").val(""); // reset form inputs
    }
  });

  $('#country').blur(function()
  {
    var country = $(this).val();
    var prefix = '';
    var carrierId = $('.carrierId').attr('id');
    specialRateChartCustomSearch(carrierId,country,prefix);
  });

  $('#prefix').blur(function()
  {
    var prefix = $(this).val();
    var country = '';
    var carrierId = $('.carrierId').attr('id');
    specialRateChartCustomSearch(carrierId,country,prefix);
  });

  // inline search ends

  // manufacturing search form
  /************************************************************************************************************************/ 
  $('#buttonRateChartSearch').attr('disabled',false);
  $('#rateChartSearchForm').submit(function(e)
  {
    
    $('#buttonRateChartSearch').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type: 'POST',
      data:  formData, //{user_name: userName, password: passWord},
      dataType: 'json',
    
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html('');
          $(this).html("Rate Chart is uploading ...<img src='/images/indicator.gif'>");
        });
      },
    
      success: function(data)
      {
         
        $('#main').fadeIn('slow','swing',function()
        {
          $(this).html(data.view);
        });

        if(data.feedback == 0)
        {
          //console.log('feedback '+data.feedback);
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("Select a partner");
          });
        }
        else if(data.feedback == -2)
        {
          $('.oprtMessage').slideUp('fast').slideDown('slow','swing',function()
          {
            $(this).html("No chart found");
          });
        }
        else
        {
          $('.oprtMessage').fadeOut('fast');
        }
        $('#buttonRateChartSearch').attr('disabled',false);
        //$('#rateSearchForm').hide();
        
        if(data.downloadLink)
        {
          $('#downloadLink').slideDown('fast','swing',function()
          {
            $(this).html(data.downloadLink);
          });  
          $('#hiddenSearchOption').show();
        }
        //if(data.showSearchInput == 1)
          //$('#hiddenSearchOption').show();
      },
    
      error: function (xhr, ajaxOptions, thrownError) 
      {
            
        $('.oprtMessage').fadeOut('fast').fadeIn('slow',function()
        {
          $(this).html(xhr.status + ': '+thrownError);
        });
        $('#buttonRateChartSearch').attr('disabled',false);
      }
    }); // .ajax ends
    return false;
  }); // $('#rateSearchForm').submit(function(e)
  // manufacturing search form ends
  /************************************************************************************************************************/ 
 
} // partnerSpecialRateChartPhp ends


function rateChartListPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#rateChartListView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#rateChartListView thead').wrap('<div class="theadContainer"></div>');  
  $('#rateChartListView tbody').wrap('<div class="tbodyContainer"></div>');
 
  var tdWidth = ['100','100','100','100'];
  
  if($('.updateTrafficTypeAccess').attr('id') == 1)
    tdWidth.push('50');
  if($('.deleteRateChartAccess').attr('id') == 1)
    tdWidth.push('50');

  tdClassAdd('#rateChartListView tbody','#rateChartListView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
 


  // inline search 

  // initialization
  if($('#searchOption').val() == 1)
    $('#hiddenCarrierTypeSearch').show();
  else if($('#searchOption').val() == 2)
    $('#hiddenTrafficTypeSearch').show();
  else if($('#searchOption').val() == 3)
    $('#hiddenCarrierNameSearch').show();

  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    $('#hiddenCarrierTypeSearch').hide();
    $('#hiddenTrafficTypeSearch').hide();
    $('#hiddenCarrierNameSearch').hide();

    if(searchOption == 1)
    {
      $('#hiddenCarrierTypeSearch').show();
      
    }
    else if(searchOption == 2)
    {
      $('#hiddenTrafficTypeSearch').show();
      
    }
    else if(searchOption == 3)
    {
      $('#hiddenCarrierNameSearch').show();
      
    }
    rateChartListSearch(baseUrl+'alveron/rateChartList',0,0,0,searchOption);
  });
  $('#carrierTypeSearch').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var carrierNameId = 0;
    var trafficTypeId = 0;
    var searchOption = 1;
    rateChartListSearch(baseUrl+'alveron/rateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });

  $('#trafficTypeSearch').bind('change',function()
  {
    var carrierTypeId = 0;
    var carrierNameId = 0;
    var trafficTypeId = $(this).val();
    var searchOption = 2;
    rateChartListSearch(baseUrl+'alveron/rateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });

  $('#carrierNameSearch').bind('change',function()
  {
    var carrierTypeId = 0;
    var carrierNameId = $(this).val();
    var trafficTypeId = 0;
    var searchOption = 3;
    rateChartListSearch(baseUrl+'alveron/rateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });
  // inline search
  $('#editForm').submit(function(e) // rate chart submit button 
  {
    $('#buttonEdit').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    $.ajax(
    {
      url:urlChange,
      type:'post',
      data:formData,
      dataType:'json',
      beforeSend:function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('Partner\'s traffic type is being changed in the rate chart ...<img src=\'/images/indicator.gif\'>');
        });
      },
      success:function(data)
      {
        $('#main').html(data.view);
        var message;
        if(data.feedback == 1)
          message = 'Partner\'s traffic type in the rate chart is updated';
        else if(data.feedback == 0)
          message = 'No data found';
        else if(data.feedback == -1)
          message = 'Failed to active the carrier status';
       
        else if(data.feedback == -3)
          message = 'Traffic type update failed';
        
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html(message);
        });
      }
    });
    return false;
  }); // rate chart submit button ends
  // rate chart delete and showing update form
  $('tbody').children('tr').each(function()
  {
   
    $(this).find('.td4').children('a').bind('click',function() // td4 is for rate chart's partner traffic typ edit
    {
      var url = $(this).attr('href');
      $.ajax(
      {
        url: url,
        type: 'post',
        dataType: 'json',
        success: function(data)
        {
          $('title').html(data.title);
          $('#main').html(data.view);
          if(data.feedback == 1)
          {
            $('#rateChartListTable').hide();
            $('#editForm').show();
            
          }  
          else
          {
            $('#editForm').hide();
            $('#rateChartListTable').show();
          }  
          
        }
      }); // edit icon click ajax ends, show the edit form
      return false;
    }); // rate chart's partner traffic type edit ends

    $(this).find('.td5').children('a').bind('click',function() // td5 is for rate chart delete
    {
      //return false;
      var url = $(this).attr('href');
     
      $.ajax(
      {
        url:url,
        type:'post',
        dataType:'json',
        beforeSend:function()
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html('Rate chart deleting ...<img src=\'/images/indicator.gif\'>');
          });
        },
        success:function(data)
        {
          $('#main').html(data.view);
          var message;
          if(data.feedback == 1)
          {
            message = 'Rate chart delete successful';
          }
          else
          {
            message = 'Rate chart delete failed';
          }
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(message);  
          });
        }
      }); // ajax ends

      return false;
  
    }); // $(this).find('.td5').children('a').click(function( ends
  }); // rate chart delete and showing update form ends
      
  
 
} // rateChartListPhp ends


function specialRateChartListPhp()
{
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#rateChartListView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#rateChartListView thead').wrap('<div class="theadContainer"></div>');  
  $('#rateChartListView tbody').wrap('<div class="tbodyContainer"></div>');
 
  var tdWidth = ['100','100','100','100'];
  
  if($('.updateTrafficTypeAccess').attr('id') == 1)
    tdWidth.push('50');
  if($('.deleteRateChartAccess').attr('id') == 1)
    tdWidth.push('50');

  tdClassAdd('#rateChartListView tbody','#rateChartListView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});
 


  // inline search 

  // initialization
  if($('#searchOption').val() == 1)
    $('#hiddenCarrierTypeSearch').show();
  else if($('#searchOption').val() == 2)
    $('#hiddenTrafficTypeSearch').show();
  else if($('#searchOption').val() == 3)
    $('#hiddenCarrierNameSearch').show();

  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    $('#hiddenCarrierTypeSearch').hide();
    $('#hiddenTrafficTypeSearch').hide();
    $('#hiddenCarrierNameSearch').hide();

    if(searchOption == 1)
    {
      $('#hiddenCarrierTypeSearch').show();
      
    }
    else if(searchOption == 2)
    {
      $('#hiddenTrafficTypeSearch').show();
      
    }
    else if(searchOption == 3)
    {
      $('#hiddenCarrierNameSearch').show();
      
    }
    rateChartListSearch(baseUrl+'alveron/specialRateChartList',0,0,0,searchOption);
  });
  $('#carrierTypeSearch').bind('change',function()
  {
    var carrierTypeId = $(this).val();
    var carrierNameId = 0;
    var trafficTypeId = 0;
    var searchOption = 1;
    rateChartListSearch(baseUrl+'alveron/specialRateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });

  $('#trafficTypeSearch').bind('change',function()
  {
    var carrierTypeId = 0;
    var carrierNameId = 0;
    var trafficTypeId = $(this).val();
    var searchOption = 2;
    rateChartListSearch(baseUrl+'alveron/specialRateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });

  $('#carrierNameSearch').bind('change',function()
  {
    var carrierTypeId = 0;
    var carrierNameId = $(this).val();
    var trafficTypeId = 0;
    var searchOption = 3;
    rateChartListSearch(baseUrl+'alveron/specialRateChartList',carrierTypeId,trafficTypeId,carrierNameId,searchOption);
    
  });
  // inline search
  $('#editForm').submit(function(e) // rate chart submit button 
  {
    $('#buttonEdit').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    $.ajax(
    {
      url:urlChange,
      type:'post',
      data:formData,
      dataType:'json',
      beforeSend:function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('Partner\'s traffic type is being changed in the top/push chart ...<img src=\'/images/indicator.gif\'>');
        });
      },
      success:function(data)
      {
        $('#main').html(data.view);
        var message;
        if(data.feedback == 1)
          message = 'Partner\'s traffic type in the top/push chart is updated';
        else if(data.feedback == 0)
          message = 'No data found';
        else if(data.feedback == -1)
          message = 'Failed to active the carrier status';
       
        else if(data.feedback == -3)
          message = 'Traffic type update failed';
        
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html(message);
        });
      }
    });
    return false;
  }); // rate chart submit button ends
  // rate chart delete and showing update form
  $('tbody').children('tr').each(function()
  {
   
    $(this).find('.td4').children('a').bind('click',function() // td4 is for rate chart's partner traffic typ edit
    {
      var url = $(this).attr('href');
      $.ajax(
      {
        url: url,
        type: 'post',
        dataType: 'json',
        success: function(data)
        {
          $('title').html(data.title);
          $('#main').html(data.view);
          if(data.feedback == 1)
          {
            $('#rateChartListTable').hide();
            $('#editForm').show();
            
          }  
          else
          {
            $('#editForm').hide();
            $('#rateChartListTable').show();
          }  
          
        }
      }); // edit icon click ajax ends, show the edit form
      return false;
    }); // rate chart's partner traffic type edit ends

    $(this).find('.td5').children('a').bind('click',function() // td5 is for rate chart delete
    {
      //return false;
      var url = $(this).attr('href');
     
      $.ajax(
      {
        url:url,
        type:'post',
        dataType:'json',
        beforeSend:function()
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html('Rate chart deleting ...<img src=\'/images/indicator.gif\'>');
          });
        },
        success:function(data)
        {
          $('#main').html(data.view);
          var message;
          if(data.feedback == 1)
          {
            message = 'Rate chart delete successful';
          }
          else
          {
            message = 'Rate chart delete failed';
          }
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html(message);  
          });
        }
      }); // ajax ends

      return false;
  
    }); // $(this).find('.td5').children('a').click(function( ends
  }); // rate chart delete and showing update form ends
      
  
 
} // specialRateChartListPhp ends
// view Page specific functions end
/************************************************/
function rateChartListSearch(url,carrierTypeId,trafficTypeId,carrierNameId,searchOption)
{
  $.ajax(
    {
      url: url,
      type: 'post',
      data: {carrierTypeId:carrierTypeId, trafficTypeId: trafficTypeId, carrierNameId: carrierNameId,searchOption:searchOption},
      dataType: 'json',
      beforeSend: function()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('Waiting to generate report ...<img src=\'/images/indicator.gif\'>')
        });
      },
      success: function(data)
      {
        $('#main').html(data.view);
        $('.oprtMessage').fadeOut('fast');
        if(data.feedback == 0)
        {
          $('.oprtMessage').fadeIn('slow','swing',function()
          {
            $(this).html('No data found');
          });

        }
      }
    });
}

function marginReportPhp()
{
  
  // manufacturing table row sort by column
  /************************************************************************************************************************/ 
  var th = $('#marginReportView').find('thead').find('th');
  tableRowSortByColumn(th);

  // manufacturing table row sort by column ends
  /************************************************************************************************************************/ 
   
  // manufacturing table body scroll and width management
  /************************************************************************************************************************/ 
  
  $('#marginReportView thead').wrap('<div class="theadContainer"></div>');  
  $('#marginReportView tbody').wrap('<div class="tbodyContainer"></div>');
  var columnCount = $('#columnCount').html();
  var tdWidth = ['250','80'];
  
  for( var columnLoopCount = 0; columnLoopCount < columnCount-3;columnLoopCount++)
  {
    tdWidth.push('80'); // Rate
 
  }  
  
  
  tdClassAdd('#marginReportView tbody','#marginReportView thead',tdWidth); 
    // tdClassAdd function is at global.js common function section; by adding this function both thead th and tbody td's are kept same width

  var tdCount = $('tbody').children('tr:first').children().length;
  var i,sumWidth = 0;
  for(i=0;i<tdCount;i++)
  { 
    sumWidth = sumWidth + parseInt(tdWidth[i]);
  }
    
  var tableWidth = (sumWidth*1.6)+'px';
  $('table.display').css({'width':tableWidth});

  // manufacturing table body scroll and width management ends
  /************************************************************************************************************************/ 

  // search button submitted
  $('#buttonMarginReport').attr('disabled',false);      
  $('#marginReportForm').submit(function(e)
  {
    
    $('#buttonMarginReport').attr('disabled',true);
    e.preventDefault();
    var formData = $(this).serialize();
    var urlChange = $(this).attr('action');
    //alert(urlChange);
    $.ajax(
    {
      url: urlChange,
      type:'post',
      data: formData,
      dataType: 'json',
      beforeSend: function ()
      {
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('Margin report is being generated ...<img src=\'/images/indicator.gif\'>');
        });
      },
      success: function(data)
      {
        //alert(data.feedback);
        if(data.feedback == 0)
        {
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html('No data found');
          });
        }
        else if(data.feedback == -2)
        {
          
          $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
          {
            $(this).html('Either country or area code must be entered');
          }); 
        }
        else
        {
          $('#main').html(data.view);
        }
        $('#buttonMarginReport').attr('disabled',false);
      }
    });
  }); // search button submitted ends

  // inline search

  // initialization
  if($('#searchOption').val() == 1)
    $('#hiddenCountrySearch').show();
  else if($('#searchOption').val() == 2)
    $('#hiddenPrefixSearch').show();
  // initialization ends

  $('#searchOption').bind('change',function()
  {
    var searchOption = $(this).val();
    //console.log(searchOption);
    
    $('#hiddenCountrySearch').hide();
    $('#hiddenPrefixSearch').hide();

    if(searchOption == 1)
    {
      $('#hiddenCountrySearch').show();
      $('#hiddenCountrySearch').find("input[type=text]").val(""); // reset form inputs

    }
    else if(searchOption == 2)
    {
      $('#hiddenPrefixSearch').show(); 
      $('#hiddenPrefixSearch').find("input[type=text]").val(""); // reset form inputs
    }
  });

  $('#countrySearch').blur(function()
  {
    var country = $(this).val();
    var prefix = '';
    var customerId = $('.customerId').attr('id');
    marginReportSearch(customerId,country,prefix);
  });

  $('#prefixSearch').blur(function()
  {
    var prefix = $(this).val();
    var country = '';
    var customerId = $('.customerId').attr('id');
    marginReportSearch(customerId,country,prefix);
  });
  // inline search ends
  
}

function marginReportSearch(customerId,country,prefix)
{
  $.ajax(
  {
    url: baseUrl+'alveron/marginReport',
    type: 'post',
    data: {customerId:customerId,country:country,prefix:prefix},
    dataType: 'json',
    beforeSend: function()
    {
      $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
      {
        $(this).html('Margin report is being generated ...<img src=\'/images/indicator.gif\'>');
      });
    },
    success: function(data)
    {
      if(data.feedback == 0)
      {
        $('#main').html(data.view);
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('No data found');
          
        });
        
      }
      else if(data.feedback == -2)
      {
        
        $('#main').html(data.view);
        $('.oprtMessage').fadeOut('fast').fadeIn('slow','swing',function()
        {
          $(this).html('Either country or area code must be entered');
        }); 
        
      }

      else
      {
        $('#main').html(data.view);

      }
      
    }
  });
}