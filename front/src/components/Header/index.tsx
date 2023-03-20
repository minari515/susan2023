import HomeIcon from "@/assets/home_FILL0_wght200_GRAD0_opsz48.svg";
import ListIcon from "@/assets/list_FILL0_wght200_GRAD0_opsz48.svg";
import { useRouter } from "next/router";
import Image from "next/image";

const Header = () => {
	const router = useRouter();
	const icons = {
		// ["/questionsList" as string]: (
		// 	<HomeIcon height='70%' fill='#ffffff' onClick={() => router.push("/")} />
		// ),
		["/question/[questionId]" as string]: (
			<ListIcon
				height='70%'
				fill='#ffffff'
				onClick={() => router.push("/questionsList")}
			/>
		),
	};
	return (
		<header className='relative w-full h-14 grid grid-cols-5 gap-2 place-items-center bg-susanBlue-100'>
			{icons[router.pathname] || <div></div>}
			<div className='relative col-span-3 w-full h-full flex items-center content-center'>
				<Image
					src='/images/Logo-horizon.svg'
					layout='fill'
					objectFit='contain'
				/>
			</div>
			<div></div>
		</header>
	);
};

export default Header;
