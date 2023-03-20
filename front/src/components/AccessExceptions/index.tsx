import { AccessExceptionsProps } from "./types";

const AccessExceptions = ({
	statusCode,
	description,
}: AccessExceptionsProps) => {
	return (
		<div className='h-screen w-full flex flex-col justify-center items-center bg-susanBlue-500'>
			<h1 className='text-9xl font-extrabold text-white tracking-widest'>
				{statusCode}
			</h1>
			<div className='bg-orange-600 px-2 text-sm rounded rotate-12 absolute'>
				{description}
			</div>
		</div>
	);
};

export default AccessExceptions;
