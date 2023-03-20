import { User } from "@/types/models";
import { UserRegistrationPayload } from "@/types/payloads";
import axios, { AxiosError } from "axios";
import { useRef, useState } from "react";

/**
 * ユーザ登録
 * @param userIdToken LIFFユーザIDトークン
 */
const useRegistration = (userIdToken?: User["token"]) => {
	const [age, setAge] = useState<UserRegistrationPayload["age"] | undefined>(
		undefined
	);
	const [gender, setGender] = useState<
		UserRegistrationPayload["gender"] | undefined
	>(undefined);
	const [canAnswer, setCanAnswer] = useState<
		UserRegistrationPayload["canAnswer"] | null
	>(null);

	const userRegistration = async () => {
		if (!(!!age && age >= 18 && age <= 99)) {
			throw new TypeError("適切な年齢を半角数字で入力してください");
		}
		if (!gender) {
			throw new TypeError("性別を選択してください");
		}
		if (canAnswer === null) {
			throw new TypeError("利用方法を選択してください");
		}
		const payload: UserRegistrationPayload = {
			userIdToken,
			canAnswer,
			age,
			gender,
		};
		try {
			const { status, data } = await axios.post<User>(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/users/registration`,
				payload
			);
			if (status === 201) {
				return;
			} else {
				throw new Error("failed to register");
			}
		} catch (error: any) {
			if (error instanceof AxiosError) {
				const { status, data } = error.response!;
			}
			throw error;
		}
	};

	return {
		age,
		setAge,
		gender,
		setGender,
		canAnswer,
		setCanAnswer,
		userRegistration,
	};
};

export default useRegistration;
