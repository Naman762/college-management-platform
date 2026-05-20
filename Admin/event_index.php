<?php
session_start();
if (!$_SESSION["LoginAdmin"])
{
    header('location:../login/login.php');
}
    require_once "../connection/connection.php";
    
require_once "db.php";

?>
<!DOCTYPE html>
<html>

<head>
    <title> ADARSH CMS</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <link rel="stylesheet" href="fullcalendar/fullcalendar.min.css" />
    <script src="fullcalendar/lib/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <script src="fullcalendar/lib/moment.min.js"></script>
    <script src="fullcalendar/fullcalendar.min.js"></script>
    <style>
        h1#demo-title {
            margin: 30px 0px 80px 0px;
            text-align: center;
        }

        table tr {
            position: relative;
        }

        #event-action-response {
            background-color: #c4c6fb;
            border: #0ab53f 1px solid;
            padding: 10px 20px;
            border-radius: 3px;
            margin-bottom: 15px;
            color: #333;
            display: none;
        }

        .fc-day-grid-event .fc-content {
            background: #617cff;
            color: #FFF;
            margin-bottom: 4px;
            padding: 3px;
            border-radius: 5px;

        }

        /* .fc-event,
        .fc-event-dot {
            background-color: #586e75;
        } */

        .fc-event,
        .fc-event-dot {
            background-color: #ededed;
        }

        .fc-event {
            border: 1px solid #fff;
        }

        .delete-event-icon {
            position: absolute;
            top: 1px;
            right: 3px;
            cursor: pointer;
            z-index: 9;
            color: white;
            background: #ff5252;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
        }

        /*Right modal*/
        .modal.left_modal .modal-dialog,
        .modal.right_modal .modal-dialog {
            position: fixed;
            margin: auto;
            width: 32%;
            height: 100vh;
            -webkit-transform: translate3d(0%, 0, 0);
            -ms-transform: translate3d(0%, 0, 0);
            -o-transform: translate3d(0%, 0, 0);
            transform: translate3d(0%, 0, 0);
        }

        .right_modal .modal-content {
            height: 100%;
        }

        .modal.right_modal.fade .modal-dialog {
            right: -50%;
            -webkit-transition: opacity 0.3s linear, right 0.3s ease-out;
            -moz-transition: opacity 0.3s linear, right 0.3s ease-out;
            -o-transition: opacity 0.3s linear, right 0.3s ease-out;
            transition: opacity 0.3s linear, right 0.3s ease-out;
        }



        .modal.right_modal.fade.show .modal-dialog {
            right: 0;
            box-shadow: 0px 0px 19px rgba(0, 0, 0, .5);
        }

        /* ----- MODAL STYLE ----- */
        .modal-content {
            border-radius: 0;
            border: none;
        }



        .modal-header.left_modal,
        .modal-header.right_modal {

            padding: 10px 15px;
            border-bottom-color:
                #EEEEEE;
            background-color:
                #FAFAFA;
        }

        .modal_outer .modal-body {
            /*height:90%;*/
            overflow-y: auto;
            overflow-x: hidden;
            height: 91vh;
        }
    </style>
</head>

<body>
    <!-- <div class="container">
        <h1 id="demo-title">AJAX-Based Event Management System with
            Bootstrap</h1>
        <div id="event-action-response"></div>
        <div id="calendar"></div>
    </div> -->
    <?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>


        <main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-4 text-white admin-dashboard pl-3">
					<h4 class="">Teacher Profile Infromation</h4>
				</div>


                <div class="container">
                    <div id="event-action-response"></div>
                    <div id="calendar"></div>
                </div>




    <!-- Display event details in a modal -->
    <div id="eventDetailsModal" class="modal modal_outer right_modal fade " tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Selected Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="eventDetails"></div>

                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button id="updateEvent" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal_outer right_modal fade " id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
                        <div class="form-group">
                            <label for="eventTitle">Event Title</label>
                            <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
                        </div>
                        <div class="form-group">
                            <label for="eventDate">Event Date</label>
                            <input type="date" class="form-control" id="eventDate" name="eventDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>
    </main>
</body>

<script>
    $(document).ready(function() {
        var calendar = $('#calendar').fullCalendar({
            editable: true,
            events: 'getEvent.php',
            selectable: true,
            selectHelper: true,
            header: {
                right: 'prevYear, prev, next, nextYear, today',
                left: 'title',
                center: 'listMonth, listYear,  month, basicWeek'
            },
            buttonText: {
                listMonth: 'List Month',
                listYear: 'List Year',
            },

            displayEventTime: true,
            // select: function(start, allDay) {
            //     var Event = prompt("Add Event");
            //     if (Event) {
            //         var Date = $.fullCalendar.formatDate(start, "Y-MM-DD");
            //         $("#event-action-response").hide();
            //         $.ajax({
            //             url: "addEvent.php",
            //             type: "POST",
            //             data: {
            //                 title: Event,
            //                 start: Date
            //             },
            //             success: function() {
            //                 calendar.fullCalendar('refetchEvents');
            //                 $("#event-action-response").html("Event added Successfully");
            //                 $("#event-action-response").show();
            //             }
            //         });
            //     }
            // },
            select: function(start, allDay) {
                var selectedDate = start.format('YYYY-MM-DD');

                // Set the selected date in the modal input
                $("#eventDate").val(selectedDate);

                $("#addEventModal").modal('show'); // Open the modal

            },
            eventRender: function(event, element) {
                // Add a delete icon to each event listing
                var deleteIcon = $("<span class='delete-event-icon' data-event-id='" + event.id + "'>&times;</span>");
                element.append(deleteIcon);

                // Attach a click event to the delete icon
                deleteIcon.click(function(event) {
                    event.stopPropagation(); // Prevent event propagation to parent elements
                    var eventId = $(this).data("event-id"); // Get the event ID from the data attribute
                    if (confirm("Are you sure you want to delete this event?")) {
                        deleteEvent(eventId);
                    }
                });
            },
            eventDrop: function(event, delta, revertFunc) {
                if (!confirm("Are you sure about to move this event?")) {
                    revertFunc();
                } else {
                    var editedDate = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                    $("#event-action-response").hide();
                    $.ajax({
                        url: "editevent.php",
                        type: "POST",
                        data: {
                            event_id: event.id,
                            start: editedDate
                        },
                        success: function(resource) {
                            calendar.fullCalendar('refetchEvents');
                            $("#event-action-response").html("Event moved Successfully");
                            $("#event-action-response").show();
                        }
                    });
                }
            },
            eventClick: function(event) {
                if (!$(event.target).hasClass('delete-event-icon')) {
                    $.ajax({
                        url: "getEventDetailSingle.php",
                        type: "GET",
                        data: {
                            event_id: event.id
                        },
                        success: function(data) {
                            $("#eventDetails").html(data);
                            $("#eventDetailsModal").modal('show');

                            // Update event functionality
                            $("#updateEvent").click(function() {
                                var updatedTitle = $("#editTitle").val();
                                var updatedStartDate = $("#editStartDate").val();
                                var event_id = $("#event_id").val();

                                $.ajax({
                                    url: "updateEvent.php",
                                    type: "POST",
                                    data: {
                                        event_id: event_id,
                                        updatedTitle: updatedTitle,
                                        updatedStartDate: updatedStartDate
                                    },
                                    success: function(response) {
                                        if (response === "Event updated successfully.") {
                                            calendar.fullCalendar('refetchEvents');
                                            $("#eventDetailsModal").modal('hide');
                                        } else {
                                            // Handle error
                                        }
                                    },
                                    error: function() {
                                        // Handle error
                                    }
                                });
                            });
                        }
                    });
                }
            }
        });



        // Handle form submission
        $("#addEventForm").submit(function(event) {
            event.preventDefault();

            var eventTitle = $("#eventTitle").val();
            var eventDate = $("#eventDate").val();

            $.ajax({
                url: "addEvent.php",
                type: "POST",
                data: {
                    title: eventTitle,
                    start: eventDate
                },
                success: function() {
                    calendar.fullCalendar('refetchEvents');

                    $("#event-action-response").html("Event added Successfully");
                    $("#event-action-response").show();

                    $("#addEventModal").modal('hide'); // Close the modal
                    $("#addEventForm").trigger('reset');

                }
            });
        });

        // Function to delete an event
        function deleteEvent(eventId) {
            $("#event-action-response").hide();
            $.ajax({
                url: "deleteEvent.php",
                type: "POST",
                data: {
                    event_id: eventId
                },
                success: function(response) {
                    if (response === "deleted") {
                        calendar.fullCalendar('refetchEvents');
                        $("#event-action-response").html("<span class='text-danger'>Event Deleted Successfully</span>");
                        $("#event-action-response").show();
                    } else {
                        // Handle error
                        console.log('error');
                    }
                },
                error: function() {
                    // Handle error
                }
            });
        }
    });
</script>

</html>