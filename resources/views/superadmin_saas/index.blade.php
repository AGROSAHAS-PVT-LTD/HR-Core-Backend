@php
    $title = 'Super Admin';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Super Admin Dashboard')

@section('content')
    @include('nav')

    <div class="container-fluid px-4 py-3">
        <!-- Header with Title and Date Filter -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h4 class="mb-3 mb-md-0 text-primary">Dashboard Overview</h4>
            
            <!-- Date Filter Controls -->
            <div class="btn-group" role="group" aria-label="Date filter">
                <input type="radio" class="btn-check" name="date-filter" id="today" value="today" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="today">Today</label>
                
                <input type="radio" class="btn-check" name="date-filter" id="this-week" value="this-week" autocomplete="off">
                <label class="btn btn-outline-primary" for="this-week">This Week</label>
                
                <input type="radio" class="btn-check" name="date-filter" id="this-month" value="this-month" autocomplete="off">
                <label class="btn btn-outline-primary" for="this-month">This Month</label>
                
                <input type="radio" class="btn-check" name="date-filter" id="this-year" value="this-year" autocomplete="off">
                <label class="btn btn-outline-primary" for="this-year">This Year</label>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- New Subscriptions Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-sync-alt text-primary fs-4"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><span class="new_subscriptions">{{ number_format($newSubscriptions, 0) }}</span></h3>
                                <p class="text-muted mb-2">New Subscriptions</p>
                                <a href="{{ route('superadmin.subscriptions') }}" class="text-primary text-decoration-none small">
                                    View details <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Business Registrations Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-building text-success fs-4"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><span class="new_registrations">{{ number_format($newBusinessRegistrations, 0) }}</span></h3>
                                <p class="text-muted mb-2">New Businesses</p>
                                <a href="{{ route('superadmin.businesses') }}" class="text-primary text-decoration-none small">
                                    View details <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Not Subscribed Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><span class="not_subscribed">{{ number_format($notSubscribed, 0) }}</span></h3>
                                <p class="text-muted mb-2">Not Subscribed</p>
                                <a href="{{ route('superadmin.businesses') }}" class="text-primary text-decoration-none small">
                                    View details <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Monthly Subscriptions Revenue</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            This Year
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="chartDropdown">
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                            <li><a class="dropdown-item" href="#">Last Year</a></li>
                            <li><a class="dropdown-item" href="#">Custom Range</a></li>
                        </ul>
                    </div>
                </div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="monthlySubscriptionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
         document.addEventListener('DOMContentLoaded', function() {
           // Date range calculation functions
            function getTodayRange() {
                let today = new Date();
                let startDate = new Date(today.setHours(0, 0, 0, 0));
                let endDate = new Date(today.setHours(23, 59, 59, 999));
                return { start: startDate, end: endDate };
            }
            
            function getThisWeekRange() {
                let today = new Date();
                let startDate = new Date(today.setDate(today.getDate() - today.getDay()));
                startDate.setHours(0, 0, 0, 0);
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 6);
                endDate.setHours(23, 59, 59, 999);
                return { start: startDate, end: endDate };
            }
            
            function getThisMonthRange() {
                let today = new Date();
                let startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                startDate.setHours(0, 0, 0, 0);
                let endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                endDate.setHours(23, 59, 59, 999);
                return { start: startDate, end: endDate };
            }
            
            function getThisYearRange() {
                let today = new Date();
                let startDate = new Date(today.getFullYear(), 0, 1);
                startDate.setHours(0, 0, 0, 0);
                let endDate = new Date(today.getFullYear(), 11, 31);
                endDate.setHours(23, 59, 59, 999);
                return { start: startDate, end: endDate };
            }
            
            // Handle date filter changes
            $('input[name="date-filter"]').on('change', function() {
                let filter = $(this).val();
                let range;
                
                switch (filter) {
                    case 'today':
                        range = getTodayRange();
                        break;
                    case 'this-week':
                        range = getThisWeekRange();
                        break;
                    case 'this-month':
                        range = getThisMonthRange();
                        break;
                    case 'this-year':
                        range = getThisYearRange();
                        break;
                }
                
                fetchFilteredData(range.start, range.end);
            });
            

            // Fetch filtered data
            function fetchFilteredData(startDate, endDate) {
                $.ajax({
                    url: '/superadmin/dashboard/',
                    type: 'GET',
                    data: {
                        start_date: startDate.toISOString(),
                        end_date: endDate.toISOString()
                    },
                    success: function(response) {
                        $('.new_subscriptions').text(response.new_subscriptions);
                        $('.new_registrations').text(response.new_registrations);
                        $('.not_subscribed').text(response.not_subscribed);
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }
            
            // Initialize chart
            $.ajax({
                url: '/superadmin/monthly-subscription-data',
                type: 'GET',
                success: function(response) {
                    const ctx = document.getElementById('monthlySubscriptionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: response.months,
                            datasets: [{
                                label: 'Subscription Revenue',
                                data: response.amounts,
                                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                                borderColor: 'rgba(13, 110, 253, 1)',
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'UGX ' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'UGX ' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                },
                error: function(error) {
                    console.error('Error fetching chart data:', error);
                }
            });
          
        });
    </script>
