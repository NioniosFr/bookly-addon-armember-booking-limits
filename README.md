# Bookly Addon - Group booking limits

Limit customer bookings based on the group they belong to, instead of the service's global limit.

This plugin is an extention to the [Customer Groups Add on](https://support.booking-wp-plugin.com/hc/en-us/articles/360000201873-Customer-Groups-Add-on)

## Installtion

Download the `.zip` version of this github repository and install it via your WordPress admin plugins section.
Once the plugin is installed, you will need to activate it (ensure bookly and bookly groups plugins are also installed and active prior to this plugin's installation).

Next, follow the [Setup section](#setup) to further configure bookly, in order to make this plugin functional.

## Setup

### Step 1

In order for this plugin to work, you need to manipulate the line that checks the booking limit and apply a WordPress filter to the value before evaluating the conditional statement.

The following diff is based on version `21.3.2` version of [Bookly plugin](https://wordpress.org/plugins/bookly-responsive-appointment-booking-tool/)

```diff
diff --git a/bookly-responsive-appointment-booking-tool/lib/entities/Service.php b/bookly-responsive-appointment-booking-tool/lib/entities/Service.php
index 4304e52..57da1b2 100644
--- a/bookly-responsive-appointment-booking-tool/lib/entities/Service.php
+++ b/bookly-responsive-appointment-booking-tool/lib/entities/Service.php
@@ -369,7 +369,8 @@ class Service extends Lib\Base\Entity
                                 $cart_count ++;
                             }
                         }
-                        if ( $db_count + $cart_count > $this->getAppointmentsLimit() ) {
+                        $limit = apply_filters( 'bookly_appointments_limit', $this->getAppointmentsLimit(), $service_id, $customer_id, $appointment_dates );
++                       if ( $db_count + $cart_count > $limit ) {
                             return true;
                         }
                     }
```

NOTE: Each time the plugin gets updated, we need to re-apply the same diff on that part of the bookly plugins code, otherwise the plugin will not function as expected and default limits will apply to all bookings.

### Step 2

Once the above filter is applied on the bookly code, we need to define our customer limits, on each group that we need to override the services' global limit.

On the `Bookly -> Customer Groups` submenu page, we select `Edit` on the group we want to override limits and set the group's `Description` to a JSON schema this plugin is able to read and work with. Once the JSON description is set, we select `Save` and the plugin begins to function as expected by limiting customers baed on their groups.

#### Example 1

Limit service with ID `1` (`Bookly -> Services` submenu page) to maximum `3` appointments when both the customer and the service are part of this group.

```json
[{"serviceId": 1, "limit": 3}]
```

#### Example 2

Limit service ID `1` (`Bookly -> Services` submenu page) to maximum `3` appointments and service ID 3 to `5` appointments.
When service 1 and 3, as well as the customer, are part of this group.

```json
[{"serviceId": 1, "limit": 3}, {"serviceId": 3, "limit": 5}]
```

__NOTE:__ Both the customer and the service must be part of a given group in order for the overriden limitation to be applied during booking time. In all other cases, the global Service's limit is applied.
