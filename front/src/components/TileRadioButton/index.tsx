import { TileRadioButtonProps } from "./types";

/**
 * @param id: ラジオボタンのID
 * @param name: ラジオボタンのname属性(これを揃えることでグループ化される)
 * @param value: ラジオボタンのvalue属性
 * @param imageComponent: ラジオボタンに追加する画像(svg)
 * @param displayText: ラジオボタンに追加するテキスト
 * @param onChange: ラジオボタンの変更時のイベントハンドラ
 * @param defaultCheckedValue: ラジオボタンの初期値
 * @returns タイル形式のラジオボタン
 */
const TileRadioButton = ({
	id,
	name,
	value,
	imageComponent,
	displayText,
	onChange,
	defaultCheckedValue,
}: TileRadioButtonProps) => {
	return (
		<div className='w-full'>
			<input
				type='radio'
				name={name}
				id={`${name}_${id}`}
				value={value}
				className='hidden peer'
				onChange={onChange}
				defaultChecked={value === defaultCheckedValue}
			/>
			<label
				htmlFor={`${name}_${id}`}
				className='cursor-pointer focus:cursor-auto flex flex-row items-center place-content-center gap-x-1 font-bold rounded-lg drop-shadow-md py-2 bg-neutral-200 dark:bg-slate-400 fill-susanBlue-100 peer-checked:text-slate-50 peer-checked:bg-susanBlue-100 peer-checked:fill-slate-50'
			>
				{imageComponent && <span>{imageComponent}</span>}
				{displayText}
			</label>
		</div>
	);
};

export default TileRadioButton;
