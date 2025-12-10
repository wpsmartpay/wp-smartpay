import { Container } from "react-bootstrap";

export default function Header({title, subtitle}){
	return (
		<div className="text-black bg-white border-bottom shadow-xs">
			<Container>
				<div className="d-flex align-items-center justify-content-between py-3">
					<div className='-mt-1.5'>
						<h2 className="text-slate-700! mb-1! mt-0! text-2xl! font-bold!">
						{ title }
						</h2>
						<p className='text-slate-500 font-medium text-sm! m-0'>{ subtitle }</p>
					</div>
					<div className=''>
						<img className='w-full h-7' src={smartpay.logo} alt="SmartPay Logo"/>
					</div>
				</div>
			</Container>
		</div>
	);
}
