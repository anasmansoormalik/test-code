<div x-data="{
    initDateRangePicker() {
        $($refs.daterange).daterangepicker({
            autoUpdateInput: false,
            ranges: {
                '{{ __('Today') }}': [moment(), moment()],
                '{{ __('Yesterday') }}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '{{ __('Last 7 Days') }}': [moment().subtract(6, 'days'), moment()],
                '{{ __('Last 30 Days') }}': [moment().subtract(29, 'days'), moment()],
                '{{ __('This Month') }}': [moment().startOf('month'), moment().endOf('month')],
                '{{ __('Last Month') }}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'DD.MM.YYYY',
                separator: ' - ',
                applyLabel: 'Anwenden',
                cancelLabel: 'Abbrechen',
                fromLabel: 'Von',
                toLabel: 'Bis',
                customRangeLabel: 'Benutzerdefiniert',
                weekLabel: 'W',
                daysOfWeek: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr',
                    'Sa'
                ],
                monthNames: ['Januar', 'Februar', 'März', 'April',
                    'Mai', 'Juni', 'Juli', 'August',
                    'September', 'Oktober', 'November',
                    'Dezember'
                ],
                firstDay: 1
            }
        }, function(start, end, label) {}.bind(this)).on('apply.daterangepicker', function(ev, picker) {
            $wire.doDateFilterStart('{{ $index }}', picker.startDate.format('YYYY-MM-DD'))
            $wire.doDateFilterEnd('{{ $index }}', picker.endDate.format('YYYY-MM-DD'))
            $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
        }).on('cancel.daterangepicker', function(ev, picker) {
            $($refs.daterange).data('daterangepicker').setStartDate(moment());
            $($refs.daterange).data('daterangepicker').setEndDate(moment());
            $($refs.daterange).val('');
            $wire.doDateFilterStart('{{ $index }}', '')
            $wire.doDateFilterEnd('{{ $index }}', '')
        }.bind(this));
    },
}" x-init="initDateRangePicker" class="flex flex-col">
    <div class="w-full relative flex">
        <input x-ref="daterange" class="form-control" type="text" />
    </div>
</div>
