I am still facing difficulties with submitting completed order into the orders table.

So now I want to tweak the logic.

Since I am able to submit drafted orders to the pos_order_drafts table, I just want to take the record/details  of the particular order in focus (which are already existing in pos_order_drafts) and add the payment method, the employee who closed it, time and date, discount, and other financial info which are not the data from the pos_order_drafts table, and collectively submit everything to the orders table.

This will help us skip the step of capturing the entire order data all over again, and simplify the submission process. As you already can see how I have been facing the type string errors above, and could not resolve that.

Now the save and close button shud be changed to choose payment method;
            <button class="btn btn-success btn-lg fw-bold" id="btnSaveCloseOrder" style="font-size:1.15rem; border-radius:12px; box-shadow:0 2px 8px rgba(39,174,96,0.08); min-width:240px;">
                <i class="bi bi-check2-circle me-2"></i>Save & Close Order
            </button>

 And Save and Close orders modal Shud become choose payment method modal and, should not contain the order details, and shud only serve as a way to confirm the which payment method used. 

Once payment method is confirmed and captured by the system, choose payment method shud be changed to save and close, where all the details get sent to the orders table.

pos_order_drafts table structure:


Now help me restructure the code to match the requirements I have described, and also make it aligned with the new database structure to support the new requirement.

Then give me the complete and updated page structures and database syntax to execute the queies/correct the db tables where necessary



------------------------------------------------------------------

Secondly I want to also make it possible for the system to prepare a sales invoice receipt that can be 
sent to the kitchen printer or printed at the payment counter (for restaurant record or on customer 
request) via the small printer machine like (Terminal Machine) with below details; - Printer Model: 
IRP-200D / POS-80C - Paper Width 800mm - Receipt: 72mm x 297mm - Connection with system: USB



/var/www/html/admin/dashboard.php on line 275
secondary">
Warning: Undefined array key "status" in /var/www/html/admin/dashboard.php on line 283

Deprecated: ucfirst(): Passing null to parameter #1 ($string) of type string is deprecated in /var/www/html/admin/dashboard.php on line 283




Fatal error: Cannot redeclare isLoggedIn() (previously declared in /var/www/html/admin/includes/functions.php:37) in /var/www/html/includes/functions.php on line 206




Fatal error: Cannot use positional argument after argument unpacking in /var/www/html/admin/includes/view_all_orders.php on line 83