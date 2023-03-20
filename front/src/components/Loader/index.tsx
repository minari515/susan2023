import Image from "next/image";

/**
 * @returns 読込中のローディングアイコン
 */
const Loader = () => (
	<div
		style={{
			position: "fixed",
			left: 0,
			top: 0,
			zIndex: 100,
			width: "100%",
			height: "100%",
			display: "flex",
			alignItems: "center",
			justifyContent: "center",
			backgroundColor: "rgba(0, 0, 0, 0.3)",
		}}
	>
		<Image
			src='/images/LINE_spinner.svg'
			width={45}
			height={45}
			alt='line loader'
		/>
	</div>
);

export default Loader;
