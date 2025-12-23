// Car Rental System JavaScript

$(document).ready(function() {
    
    // Form validation
    $('form').submit(function() {
        var password = $('#password');
        var confirmPassword = $('#confirm_password');
        
        if (password.length && confirmPassword.length) {
            if (password.val() !== confirmPassword.val()) {
                alert('Passwords do not match!');
                return false;
            }
        }
        return true;
    });
    
    // Date picker initialization (if using date inputs)
    $('input[type="date"]').each(function() {
        var today = new Date().toISOString().split('T')[0];
        $(this).attr('min', today);
    });
    
    // Calculate rental cost
    $('#calculate_cost').click(function() {
        var dailyRate = parseFloat($('#daily_rate').val());
        var pickupDate = new Date($('#pickup_date').val());
        var returnDate = new Date($('#return_date').val());
        
        if (isNaN(dailyRate) || !pickupDate.getTime() || !returnDate.getTime()) {
            alert('Please enter valid dates and daily rate');
            return;
        }
        
        var timeDiff = returnDate.getTime() - pickupDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (daysDiff <= 0) {
            alert('Return date must be after pickup date');
            return;
        }
        
        var totalCost = dailyRate * daysDiff;
        $('#total_days').val(daysDiff);
        $('#total_cost').val(totalCost.toFixed(2));
        $('#total_cost_display').text('$' + totalCost.toFixed(2));
    });
    
    // Auto-calculate when dates change
    $('#pickup_date, #return_date').change(function() {
        if ($('#pickup_date').val() && $('#return_date').val()) {
            $('#calculate_cost').click();
        }
    });
    
    // Toggle password visibility
    $('.toggle-password').click(function() {
        var input = $($(this).data('target'));
        var icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Confirm before deleting
    $('.delete-btn').click(function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').click(function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });
});

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Validate email
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Show notification
function showNotification(message, type = 'success') {
    var alertClass = 'alert-' + type;
    var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                        message +
                        '<button type="button" class="close" data-dismiss="alert">' +
                        '<span>&times;</span>' +
                        '</button>' +
                        '</div>');
    
    $('.container').prepend(notification);
    
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}