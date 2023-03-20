import { useRouter } from "next/router";
import { useEffect, useState } from "react";
import { LoginButtonProps } from "./types";
import Image from "next/image";
import loginButtonImage from "@/assets/line-login-button@1.5x.png";

const LoginButton = ({ disabled = false }: LoginButtonProps) => {
	const router = useRouter();
	const [disabledState, setDisabledState] = useState(disabled);

	useEffect(() => {
		setDisabledState(disabled);
	}, [disabled]);

	const clickedHandler = () => {
		setDisabledState(true);
		router.push(
			`https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/?disable_auto_login=true`
		);
	};

	return (
		<button
			className={`relative w-48 h-12 ${disabledState ? "opacity-50" : ""}`}
			disabled={disabledState}
			onClick={clickedHandler}
		>
			<Image
				src={loginButtonImage}
				alt='LINEでログイン'
				layout='fill'
				objectFit='contain'
			/>
		</button>
	);
};

export default LoginButton;
