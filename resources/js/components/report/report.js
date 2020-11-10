import Chart from 'react-apexcharts'
export const Report = ({ options, series, type = 'bar', width = '100%' }) => {
    return <Chart options={options} series={series} type={type} width={width} />
}
