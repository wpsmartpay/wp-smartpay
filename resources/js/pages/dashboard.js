import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Report } from '../components/report/report'

import dayjs from 'dayjs'
const relativeTime = require('dayjs/plugin/relativeTime')
let utc = require('dayjs/plugin/utc')
dayjs.extend(relativeTime)
dayjs.extend(utc)

export const Dashboard = () => {
    const [report, setReport] = useState({
        monthlyReport: [],
        recentPayments: [],
    })

    useEffect(() => {
        apiFetch({
            path: `${smartpay.restUrl}/v1/reports`,
            headers: {
                'X-WP-Nonce': smartpay.apiNonce,
            },
        }).then((response) => {
            setReport({
                monthlyReport: response.monthly_report,
                recentPayments: response.recent_payments,
            })
        })
    }, [])

    return (
        <>
            <div className="smartpay-dashboard-header">
                <div className="smartpay-dashboard-header__inner">
                    <h2 className="smartpay-dashboard-header__title">
                        {__('SmartPay', 'smartpay')}
                    </h2>
                </div>
            </div>

            <div className="smartpay-dashboard">
                <div className="smartpay-dashboard__grid">
                    {/* Report Chart */}
                    <div className="smartpay-dashboard__chart">
                        <div className="smartpay-dashboard__section">
                            <h2 className="smartpay-dashboard__section-title">
                                {__('Monthly Report', 'smartpay')}
                            </h2>
                            <div className="smartpay-dashboard__card">
                                <Report
                                    height="350"
                                    series={[
                                        {
                                            name: __('Product Purchase', 'smartpay'),
                                            data: report?.monthlyReport.map(
                                                (data) => data.product_purchase
                                            ),
                                        },
                                        {
                                            name: __('Form Payment', 'smartpay'),
                                            data: report?.monthlyReport.map(
                                                (data) => data.form_payment
                                            ),
                                        },
                                    ]}
                                    options={{
                                        chart: {
                                            type: 'bar',
                                            height: 350,
                                            stacked: true,
                                            toolbar: {
                                                show: true,
                                            },
                                        },
                                        plotOptions: {
                                            bar: {
                                                horizontal: false,
                                                columnWidth: '60%',
                                                borderRadius: 4,
                                            },
                                        },
                                        colors: ['#3858e9', '#22c55e'],
                                        dataLabels: {
                                            enabled: false,
                                        },
                                        xaxis: {
                                            categories: report?.monthlyReport.map(
                                                (data) => data.date
                                            ),
                                        },
                                        yaxis: {
                                            title: {
                                                text: __('Revenue', 'smartpay'),
                                            },
                                        },
                                        legend: {
                                            position: 'bottom',
                                            offsetY: 10,
                                        },
                                        fill: {
                                            opacity: 1,
                                        },
                                        grid: {
                                            borderColor: '#e5e7eb',
                                        },
                                    }}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Recent Payments Sidebar */}
                    <div className="smartpay-dashboard__sidebar">
                        <div className="smartpay-dashboard__section">
                            <h2 className="smartpay-dashboard__section-title">
                                {__('Recent Payments', 'smartpay')}
                            </h2>
                            <div className="smartpay-dashboard__card">
                                <ul className="smartpay-recent-payments">
                                    {!report.recentPayments.length && (
                                        <li className="smartpay-recent-payments__empty">
                                            {__('No payment found', 'smartpay')}
                                        </li>
                                    )}
                                    {report.recentPayments.map((payment) => (
                                        <li
                                            className="smartpay-recent-payments__item"
                                            key={payment.id}
                                        >
                                            <span className="smartpay-recent-payments__detail">
                                                {`$${payment.amount} paid by ${payment.email}`}
                                            </span>
                                            <span className="smartpay-recent-payments__time">
                                                {dayjs.utc(payment.created_at).fromNow()}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}
