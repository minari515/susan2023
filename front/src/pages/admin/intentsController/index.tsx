import AccessExceptions from "@/components/AccessExceptions";
import { AuthContext } from "@/contexts/AuthContext";
import DefaultLayout from "@/layouts/Default";
import { useContext, useEffect } from "react";

const IntentsController = () => {
	const { user } = useContext(AuthContext);

	return user?.type !== "instructor" ? (
		<AccessExceptions statusCode={403} description='Forbidden Access' />
	) : (
		<DefaultLayout>
			<h1>IntentsController(開発予定...)</h1>
		</DefaultLayout>
	);
};

export default IntentsController;
